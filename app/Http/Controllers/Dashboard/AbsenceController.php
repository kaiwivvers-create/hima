<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Attendance;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AbsenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:absences.update')->only(['edit', 'update', 'approve', 'reject']);
        $this->middleware('permission:absences.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user && ! in_array($user->role, ['parent', 'student'], true) && ! $this->hasPermission($user, 'absences.view')) {
            abort(403);
        }
        $query = Absence::with('student')->latest('start_date');

        if ($user && $user->role === 'student') {
            $query->where('student_id', $user->id);
        }

        return view('dashboard.absences.index', [
            'absences' => $query->paginate(10),
            'students' => $this->resolveStudentOptions($user),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        if ($user && ! in_array($user->role, ['parent', 'student'], true) && ! $this->hasPermission($user, 'absences.create')) {
            abort(403);
        }
        $students = $this->resolveStudentOptions($user);

        return view('dashboard.absences.create', [
            'students' => $students,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user && ! in_array($user->role, ['parent', 'student'], true) && ! $this->hasPermission($user, 'absences.create')) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
            'verification_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'submitted_by' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        if ($user) {
            if ($user->role === 'student') {
                $validated['student_id'] = $user->id;
            }
            if ($user->role === 'parent') {
                $allowedStudentIds = DB::table('parent_student')
                    ->where('parent_user_id', $user->id)
                    ->pluck('student_user_id')
                    ->all();
                if (!in_array((int) $validated['student_id'], $allowedStudentIds, true)) {
                    abort(403);
                }
            }
            if (in_array($user->role, ['student', 'parent'], true)) {
                $validated['submitted_by'] = $user->email;
                $validated['verification_status'] = 'pending';
            }
        }

        $absence = Absence::create($validated);

        ActivityLogger::log(
            'absence.created',
            'absence',
            $absence->id,
            'Absence created.',
            null,
            ActivityLogger::snapshot($absence, 'absence')
        );

        if ($absence->verification_status === 'approved') {
            $this->applyExcusedRange($absence);
        }

        return redirect()->route('dashboard.absences.index', ['lang' => app()->getLocale()])
            ->with('success', 'Absence record created successfully.');
    }

    public function edit(Absence $absence): View
    {
        $user = request()->user();
        if ($user && $user->role === 'student' && (int) $absence->student_id !== (int) $user->id) {
            abort(403);
        }

        return view('dashboard.absences.edit', [
            'absence' => $absence,
            'students' => $this->resolveStudentOptions($user),
        ]);
    }

    public function update(Request $request, Absence $absence): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string'],
            'verification_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'submitted_by' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        if ($user && $user->role === 'student') {
            if ((int) $absence->student_id !== (int) $user->id) {
                abort(403);
            }
            $validated['student_id'] = $user->id;
        }

        if ($user && in_array($user->role, ['student', 'parent'], true)) {
            $validated['submitted_by'] = $user->email;
            $validated['verification_status'] = 'pending';
        }

        $previousState = [
            'student_id' => $absence->student_id,
            'start_date' => $absence->start_date?->toDateString(),
            'end_date' => $absence->end_date?->toDateString(),
            'verification_status' => $absence->verification_status,
        ];

        $before = ActivityLogger::snapshot($absence, 'absence');
        $absence->update($validated);

        ActivityLogger::log(
            'absence.updated',
            'absence',
            $absence->id,
            'Absence updated.',
            $before,
            ActivityLogger::snapshot($absence, 'absence')
        );

        if ($previousState['verification_status'] === 'approved') {
            $this->clearSyncedAttendance(
                $previousState['student_id'],
                $previousState['start_date'],
                $previousState['end_date'],
                $absence->id
            );
        }

        if ($absence->verification_status === 'approved') {
            $this->applyExcusedRange($absence);
        }

        return redirect()->route('dashboard.absences.index', ['lang' => app()->getLocale()])
            ->with('success', 'Absence record updated successfully.');
    }

    public function destroy(Absence $absence): RedirectResponse
    {
        $user = request()->user();
        if ($user && $user->role === 'student' && (int) $absence->student_id !== (int) $user->id) {
            abort(403);
        }

        $before = ActivityLogger::snapshot($absence, 'absence');

        if ($absence->verification_status === 'approved') {
            $this->clearSyncedAttendance(
                $absence->student_id,
                $absence->start_date?->toDateString(),
                $absence->end_date?->toDateString(),
                $absence->id
            );
        }

        $absence->delete();

        ActivityLogger::log(
            'absence.deleted',
            'absence',
            $absence->id,
            'Absence deleted.',
            $before,
            null
        );

        return redirect()->route('dashboard.absences.index', ['lang' => app()->getLocale()])
            ->with('success', 'Absence record deleted successfully.');
    }

    public function approve(Absence $absence): RedirectResponse
    {
        return $this->setVerificationStatus($absence, 'approved', 'Absence approved.');
    }

    public function reject(Absence $absence): RedirectResponse
    {
        return $this->setVerificationStatus($absence, 'rejected', 'Absence rejected.');
    }

    private function applyExcusedRange(Absence $absence): void
    {
        if (!$absence->start_date || !$absence->end_date) {
            return;
        }

        $note = $this->absenceNotePrefix($absence->id).$absence->reason;
        $period = CarbonPeriod::create($absence->start_date, $absence->end_date);

        foreach ($period as $date) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $absence->student_id,
                    'attendance_date' => $date->format('Y-m-d'),
                ],
                [
                    'status' => 'excused',
                    'notes' => $note,
                ]
            );
        }
    }

    private function clearSyncedAttendance(int $studentId, ?string $startDate, ?string $endDate, int $absenceId): void
    {
        if (!$startDate || !$endDate) {
            return;
        }

        $notePrefix = $this->absenceNotePrefix($absenceId);
        Attendance::where('student_id', $studentId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('notes', 'like', "{$notePrefix}%")
            ->delete();
    }

    private function absenceNotePrefix(int $absenceId): string
    {
        return 'Excused (absence #'.$absenceId.'): ';
    }

    private function resolveStudentOptions(?User $user): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::where('role', 'student')->orderBy('name');
        if ($user && $user->role === 'student') {
            return $query->where('id', $user->id)->get();
        }

        if ($user && $user->role === 'parent') {
            $allowedIds = DB::table('parent_student')
                ->where('parent_user_id', $user->id)
                ->pluck('student_user_id');

            $query->whereIn('id', $allowedIds->all());
        }

        return $query->get();
    }

    private function hasPermission(User $user, string $permission): bool
    {
        if ($user->role === 'super admin') {
            return true;
        }

        $roleId = DB::table('roles')->where('name', $user->role)->value('id');
        if (!$roleId) {
            return false;
        }

        return DB::table('role_permission')
            ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
            ->where('role_permission.role_id', $roleId)
            ->where('permissions.name', $permission)
            ->exists();
    }

    private function setVerificationStatus(Absence $absence, string $status, string $successMessage): RedirectResponse
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            abort(422);
        }

        $previousStatus = $absence->verification_status;
        if ($previousStatus === $status) {
            return redirect()->route('dashboard.absences.index', ['lang' => app()->getLocale()])
                ->with('success', $successMessage);
        }

        $before = ActivityLogger::snapshot($absence, 'absence');
        $absence->update(['verification_status' => $status]);

        ActivityLogger::log(
            'absence.updated',
            'absence',
            $absence->id,
            'Absence verification set to '.$status.'.',
            $before,
            ActivityLogger::snapshot($absence, 'absence')
        );

        if ($previousStatus === 'approved') {
            $this->clearSyncedAttendance(
                $absence->student_id,
                $absence->start_date?->toDateString(),
                $absence->end_date?->toDateString(),
                $absence->id
            );
        }

        if ($status === 'approved') {
            $this->applyExcusedRange($absence);
        }

        return redirect()->route('dashboard.absences.index', ['lang' => app()->getLocale()])
            ->with('success', $successMessage);
    }
}
