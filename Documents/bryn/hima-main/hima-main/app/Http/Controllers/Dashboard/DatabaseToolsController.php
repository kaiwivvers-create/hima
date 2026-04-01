<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseToolsController extends Controller
{
    private const BACKUP_DIR = 'db_backups';

    public function __construct()
    {
        $this->middleware('permission:admin.database.manage');
    }

    public function index(): View
    {
        $files = collect(Storage::disk('local')->files(self::BACKUP_DIR))
            ->filter(fn (string $path): bool => str_ends_with($path, '.json'))
            ->map(function (string $path): array {
                return [
                    'name' => basename($path),
                    'path' => $path,
                    'size' => Storage::disk('local')->size($path),
                    'updated_at' => Storage::disk('local')->lastModified($path),
                ];
            })
            ->sortByDesc('updated_at')
            ->values();

        return view('dashboard.admin.database.index', [
            'backups' => $files,
        ]);
    }

    public function backup(): RedirectResponse
    {
        $path = $this->createBackup();
        $filename = basename($path);

        ActivityLogger::log(
            'database.backup.created',
            'database',
            1,
            'Created database backup: '.$filename,
            null,
            ['file' => $filename]
        );

        return back()->with('success', 'Database backup created: '.$filename);
    }

    public function download(string $file): StreamedResponse
    {
        $safeFile = basename($file);
        if (!preg_match('/^[A-Za-z0-9._-]+\.json$/', $safeFile)) {
            abort(404);
        }

        $path = self::BACKUP_DIR.'/'.$safeFile;
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'max:20480', 'mimes:json,txt', 'mimetypes:application/json,text/plain,application/octet-stream'],
        ]);

        $raw = file_get_contents($validated['backup_file']->getRealPath());
        if ($raw === false) {
            return back()->withErrors(['backup_file' => 'Could not read uploaded backup file.']);
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload) || !isset($payload['tables']) || !is_array($payload['tables'])) {
            return back()->withErrors(['backup_file' => 'Invalid backup format.']);
        }

        $tableCount = count($payload['tables']);
        $this->restoreFromBackupPayload($payload);

        ActivityLogger::log(
            'database.backup.imported',
            'database',
            1,
            'Imported database backup from uploaded file.',
            null,
            ['tables' => $tableCount]
        );

        return back()->with('success', 'Database imported successfully.');
    }

    public function restore(string $file): RedirectResponse
    {
        $safeFile = basename($file);
        if (!preg_match('/^[A-Za-z0-9._-]+\.json$/', $safeFile)) {
            abort(404);
        }

        $path = self::BACKUP_DIR.'/'.$safeFile;
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $payload = json_decode((string) Storage::disk('local')->get($path), true);
        if (!is_array($payload) || !isset($payload['tables']) || !is_array($payload['tables'])) {
            return back()->withErrors(['backup_file' => 'Selected backup file is invalid.']);
        }

        $this->restoreFromBackupPayload($payload);

        ActivityLogger::log(
            'database.backup.restored',
            'database',
            1,
            'Restored database from backup file: '.$safeFile,
            null,
            ['file' => $safeFile, 'tables' => count($payload['tables'])]
        );

        return back()->with('success', 'Database restored from '.$safeFile.'.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_text' => ['required', 'in:RESET DATABASE'],
        ]);

        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        ActivityLogger::log(
            'database.reset',
            'database',
            1,
            'Database reset with migrate:fresh --seed.',
            null,
            ['seeded' => true]
        );

        return back()->with('success', 'Database has been reset and seeded.');
    }

    private function createBackup(): string
    {
        $disk = Storage::disk('local');
        if (!$disk->exists(self::BACKUP_DIR)) {
            $disk->makeDirectory(self::BACKUP_DIR);
        }

        $tables = $this->getBackupTables();
        $payload = [
            'created_at' => now()->toIso8601String(),
            'connection' => DB::getDefaultConnection(),
            'tables' => [],
        ];

        foreach ($tables as $table) {
            $rows = DB::table($table)->get()->map(fn ($row): array => (array) $row)->all();
            $payload['tables'][] = [
                'name' => $table,
                'rows' => $rows,
            ];
        }

        $filename = 'backup_'.now()->format('Ymd_His').'.json';
        $path = self::BACKUP_DIR.'/'.$filename;
        $disk->put($path, json_encode($payload, JSON_PRETTY_PRINT));

        return $path;
    }

    private function restoreFromBackupPayload(array $payload): void
    {
        $currentTables = collect(Schema::getTableListing())->map(fn ($table): string => strtolower($table))->all();
        $imports = collect($payload['tables'])
            ->filter(fn ($entry): bool => is_array($entry) && isset($entry['name']) && isset($entry['rows']) && is_array($entry['rows']))
            ->filter(fn ($entry): bool => in_array(strtolower((string) $entry['name']), $currentTables, true))
            ->values();

        DB::transaction(function () use ($imports): void {
            $this->disableForeignKeyChecks();

            try {
                foreach ($imports as $entry) {
                    DB::table($entry['name'])->delete();
                }

                foreach ($imports as $entry) {
                    $rows = collect($entry['rows'])
                        ->filter(fn ($row): bool => is_array($row))
                        ->values();

                    if ($rows->isEmpty()) {
                        continue;
                    }

                    foreach ($rows->chunk(300) as $chunk) {
                        DB::table($entry['name'])->insert($chunk->all());
                    }
                }
            } finally {
                $this->enableForeignKeyChecks();
            }
        });
    }

    private function getBackupTables(): array
    {
        return collect(Schema::getTableListing())
            ->reject(fn (string $table): bool => str_starts_with($table, 'sqlite_'))
            ->values()
            ->all();
    }

    private function disableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = replica');
        }
    }

    private function enableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = DEFAULT');
        }
    }
}
