@extends('dashboard.layout')

@section('title', 'Database Tools')
@section('page_title', 'Database Tools')

@section('content')
<section class="card" style="margin-bottom:.9rem;">
    <h2 style="margin:.1rem 0 .5rem;font-size:1.05rem;">Create Backup</h2>
    <p class="muted" style="margin:0 0 .7rem;">Creates a JSON snapshot of all application tables.</p>
    <form method="POST" action="{{ route('dashboard.admin.database.backup', ['lang' => app()->getLocale()]) }}">
        @csrf
        <button type="submit" class="btn">Create Backup</button>
    </form>
</section>

<section class="card" style="margin-bottom:.9rem;">
    <h2 style="margin:.1rem 0 .5rem;font-size:1.05rem;">Import Backup</h2>
    <p class="muted" style="margin:0 0 .7rem;">Upload a backup JSON file to replace current table data.</p>
    <form method="POST" action="{{ route('dashboard.admin.database.import', ['lang' => app()->getLocale()]) }}" enctype="multipart/form-data">
        @csrf
        <div class="field" style="max-width:420px;">
            <label for="backup_file">Backup File</label>
            <input id="backup_file" type="file" name="backup_file" accept=".json,application/json,text/plain" required>
            @error('backup_file')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn">Import Backup</button>
    </form>
</section>

<section class="card" style="margin-bottom:.9rem; border-color:#b2401f; background:#fff0e9;">
    <h2 style="margin:.1rem 0 .5rem;font-size:1.05rem;color:#7f2307;">Reset Database</h2>
    <p class="muted" style="margin:0 0 .7rem;">
        Runs <code>migrate:fresh --seed</code>. This deletes existing data and recreates schema + seed data.
    </p>
    <form method="POST" action="{{ route('dashboard.admin.database.reset', ['lang' => app()->getLocale()]) }}">
        @csrf
        <div class="field" style="max-width:320px;">
            <label for="confirm_text">Type <code>RESET DATABASE</code> to continue</label>
            <input id="confirm_text" name="confirm_text" type="text" required>
            @error('confirm_text')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-danger">Reset Database</button>
    </form>
</section>

<section class="card">
    <h2 style="margin:.1rem 0 .5rem;font-size:1.05rem;">Backups</h2>
    <table class="table">
        <thead>
            <tr>
                <th>File</th>
                <th>Size</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($backups as $backup)
                <tr>
                    <td>{{ $backup['name'] }}</td>
                    <td>{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                    <td>{{ \Carbon\Carbon::createFromTimestamp($backup['updated_at'])->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <div class="actions">
                            <a class="btn-outline" href="{{ route('dashboard.admin.database.download', ['file' => $backup['name'], 'lang' => app()->getLocale()]) }}">Download</a>
                            <form method="POST" action="{{ route('dashboard.admin.database.restore', ['file' => $backup['name'], 'lang' => app()->getLocale()]) }}" onsubmit="return confirm('Restore database from this backup? This will replace current table data.');">
                                @csrf
                                <button type="submit" class="btn">Restore</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">No backups found yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
