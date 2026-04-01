<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:attendance.view')->only(['index']);
        $this->middleware('permission:attendance.mark')->only(['mark']);
        $this->middleware('permission:attendance.create')->only(['create', 'store']);
        $this->middleware('permission:attendance.update')->only(['edit', 'update']);
        $this->middleware('permission:attendance.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $attendanceDate = $request->query('date', now()->toDateString());
        $weekDate = $request->query('week', $attendanceDate);
        $search = trim((string) $request->query('search', ''));

        $dayKey = strtolower(\Carbon\Carbon::parse($attendanceDate)->format('D'));
        $dayKey = substr($dayKey, 0, 3);

        $studentsQuery = User::query()
            ->where('role', 'student')
            ->where(function ($query) use ($dayKey) {
                $query->whereNull('schedule_days')
                    ->orWhereJsonContains('schedule_days', $dayKey);
            })
            ->orderBy('name');

        if ($search !== '') {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $students = $studentsQuery->paginate(10)->withQueryString();

        $records = Attendance::whereDate('attendance_date', $attendanceDate)
            ->get()
            ->keyBy('student_id');

        $weekStart = \Carbon\Carbon::parse($weekDate)->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekEnd = (clone $weekStart)->endOfWeek(\Carbon\Carbon::SUNDAY);

        $weeklyQuery = Attendance::whereBetween('attendance_date', [
            $weekStart->toDateString(),
            $weekEnd->toDateString(),
        ]);

        if ($search !== '') {
            $studentIds = User::where('role', 'student')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })
                ->pluck('id')
                ->all();

            $weeklyQuery->whereIn('student_id', $studentIds);
        }

        $weeklyRecords = $weeklyQuery->get();
        $weeklyByStudent = $weeklyRecords->groupBy('student_id');
        $weeklyStudentIds = $weeklyByStudent->keys()->all();

        $weeklyStudents = collect();
        if (!empty($weeklyStudentIds)) {
            $weeklyStudents = User::whereIn('id', $weeklyStudentIds)
                ->orderBy('name')
                ->get()
                ->map(function (User $student) use ($weeklyByStudent) {
                    $records = $weeklyByStudent->get($student->id) ?? collect();
                    $byDate = $records->keyBy(fn ($record) => $record->attendance_date->format('Y-m-d'));

                    return [
                        'student' => $student,
                        'byDate' => $byDate,
                    ];
                });
        }

        $perPage = 5;
        $page = LengthAwarePaginator::resolveCurrentPage('week_page');
        $weeklyPage = new LengthAwarePaginator(
            $weeklyStudents->forPage($page, $perPage)->values(),
            $weeklyStudents->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'week_page',
                'query' => $request->query(),
            ]
        );

        return view('dashboard.attendances.index', [
            'students' => $students,
            'attendanceDate' => $attendanceDate,
            'records' => $records,
            'weekDate' => $weekDate,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weeklyPage' => $weeklyPage,
            'search' => $search,
        ]);
    }

    public function mark(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['present', 'sick', 'excused', 'absent'])],
            'notes' => ['nullable', 'string'],
        ]);

        $student = User::findOrFail($validated['student_id']);
        $dayKey = strtolower(\Carbon\Carbon::parse($validated['attendance_date'])->format('D'));
        $dayKey = substr($dayKey, 0, 3);
        $schedule = $student->schedule_days ?? [];
        if (!empty($schedule) && !in_array($dayKey, $schedule, true)) {
            return redirect()->route('dashboard.attendances.index', [
                'lang' => app()->getLocale(),
                'date' => $validated['attendance_date'],
            ])->with('success', 'Student is not scheduled for this day.');
        }

        $existing = Attendance::where('student_id', $validated['student_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->first();
        $before = $existing ? ActivityLogger::snapshot($existing, 'attendance') : null;

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'attendance_date' => $validated['attendance_date'],
            ],
            [
                'status' => $this->toDatabaseStatus($validated['status']),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        ActivityLogger::log(
            $existing ? 'attendance.updated' : 'attendance.created',
            'attendance',
            $attendance->id,
            ($existing ? 'Attendance updated' : 'Attendance created').' for '.$student->email,
            $before,
            ActivityLogger::snapshot($attendance, 'attendance')
        );

        return redirect()->route('dashboard.attendances.index', [
            'lang' => app()->getLocale(),
            'date' => $validated['attendance_date'],
        ])->with('success', 'Attendance saved.');
    }

    public function create(): View
    {
        return view('dashboard.attendances.create', [
            'students' => User::where('role', 'student')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'attendance_date' => [
                'required',
                'date',
                Rule::unique('attendances', 'attendance_date')->where(
                    fn ($query) => $query->where('student_id', $request->input('student_id'))
                ),
            ],
            'status' => ['required', Rule::in(['present', 'late', 'absent', 'excused'])],
            'notes' => ['nullable', 'string'],
        ]);

        $attendance = Attendance::create($validated);

        ActivityLogger::log(
            'attendance.created',
            'attendance',
            $attendance->id,
            'Attendance created.',
            null,
            ActivityLogger::snapshot($attendance, 'attendance')
        );

        return redirect()->route('dashboard.attendances.index', ['lang' => app()->getLocale()])
            ->with('success', 'Attendance record created successfully.');
    }

    public function edit(Attendance $attendance): View
    {
        return view('dashboard.attendances.edit', [
            'attendance' => $attendance,
            'students' => User::where('role', 'student')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'attendance_date' => [
                'required',
                'date',
                Rule::unique('attendances', 'attendance_date')
                    ->ignore($attendance->id)
                    ->where(fn ($query) => $query->where('student_id', $request->input('student_id'))),
            ],
            'status' => ['required', Rule::in(['present', 'late', 'absent', 'excused'])],
            'notes' => ['nullable', 'string'],
        ]);

        $before = ActivityLogger::snapshot($attendance, 'attendance');
        $attendance->update($validated);

        ActivityLogger::log(
            'attendance.updated',
            'attendance',
            $attendance->id,
            'Attendance updated.',
            $before,
            ActivityLogger::snapshot($attendance, 'attendance')
        );

        return redirect()->route('dashboard.attendances.index', ['lang' => app()->getLocale()])
            ->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $before = ActivityLogger::snapshot($attendance, 'attendance');
        $attendance->delete();

        ActivityLogger::log(
            'attendance.deleted',
            'attendance',
            $attendance->id,
            'Attendance deleted.',
            $before,
            null
        );

        return redirect()->route('dashboard.attendances.index', ['lang' => app()->getLocale()])
            ->with('success', 'Attendance record deleted successfully.');
    }

    private function toDatabaseStatus(string $status): string
    {
        return match ($status) {
            'sick' => 'late',
            default => $status,
        };
    }
}
