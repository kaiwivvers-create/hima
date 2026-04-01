<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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
        $user = $request->user();
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

        $showStudentAttendanceSummary = (bool) ($user && ! in_array($user->role, ['parent', 'student'], true));
        $studentAttendanceSummary = collect();
        if ($showStudentAttendanceSummary) {
            $summaryStudents = $students->getCollection();
            $studentIds = $summaryStudents->pluck('id')->all();
            if (empty($studentIds)) {
                $studentStats = collect();
            } else {
                $studentStats = Attendance::query()
                    ->select(
                        'student_id',
                        DB::raw('COUNT(*) as total_attendance'),
                        DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count"),
                        DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count"),
                        DB::raw("SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count"),
                        DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count")
                    )
                    ->whereIn('student_id', $studentIds)
                    ->groupBy('student_id')
                    ->get()
                    ->keyBy('student_id');
            }

            $studentAttendanceSummary = $summaryStudents->mapWithKeys(function (User $student) use ($studentStats) {
                $stats = $studentStats->get($student->id);
                $totalAttendance = (int) ($stats?->total_attendance ?? 0);
                $presentCount = (int) ($stats?->present_count ?? 0);
                $lateCount = (int) ($stats?->late_count ?? 0);
                $excusedCount = (int) ($stats?->excused_count ?? 0);
                $absentCount = (int) ($stats?->absent_count ?? 0);
                $attendanceRate = $totalAttendance > 0
                    ? round((($presentCount + $lateCount + $excusedCount) / $totalAttendance) * 100, 1)
                    : 0.0;

                return [
                    $student->id => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'total_attendance' => $totalAttendance,
                        'present_count' => $presentCount,
                        'late_count' => $lateCount,
                        'excused_count' => $excusedCount,
                        'absent_count' => $absentCount,
                        'attendance_rate' => $attendanceRate,
                    ],
                ];
            });
        }

        $records = Attendance::whereDate('attendance_date', $attendanceDate)
            ->get()
            ->keyBy('student_id');

        $weekStart = \Carbon\Carbon::parse($weekDate)->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekEnd = (clone $weekStart)->endOfWeek(\Carbon\Carbon::SUNDAY);
        $weekDayKeys = collect(range(0, 6))
            ->map(fn (int $offset): string => strtolower($weekStart->copy()->addDays($offset)->format('D')))
            ->map(fn (string $day): string => substr($day, 0, 3))
            ->unique()
            ->values()
            ->all();

        $weeklyStudentsQuery = User::query()
            ->where('role', 'student')
            ->where(function ($query) use ($weekDayKeys) {
                $query->whereNull('schedule_days');
                foreach ($weekDayKeys as $dayKey) {
                    $query->orWhereJsonContains('schedule_days', $dayKey);
                }
            })
            ->orderBy('name');

        if ($search !== '') {
            $weeklyStudentsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $weeklyStudents = $weeklyStudentsQuery->get();
        $weeklyStudentIds = $weeklyStudents->pluck('id')->all();

        $weeklyByStudent = collect();
        if (!empty($weeklyStudentIds)) {
            $weeklyByStudent = Attendance::whereIn('student_id', $weeklyStudentIds)
                ->whereBetween('attendance_date', [
                    $weekStart->toDateString(),
                    $weekEnd->toDateString(),
                ])
                ->get()
                ->groupBy('student_id');
        }

        $weeklyRows = $weeklyStudents->map(function (User $student) use ($weeklyByStudent) {
            $records = $weeklyByStudent->get($student->id) ?? collect();
            $byDate = $records->keyBy(fn ($record) => $record->attendance_date->format('Y-m-d'));

            return [
                'student' => $student,
                'byDate' => $byDate,
            ];
        });

        $perPage = 5;
        $page = LengthAwarePaginator::resolveCurrentPage('week_page');
        $weeklyPage = new LengthAwarePaginator(
            $weeklyRows->forPage($page, $perPage)->values(),
            $weeklyRows->count(),
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
            'showStudentAttendanceSummary' => $showStudentAttendanceSummary,
            'studentAttendanceSummary' => $studentAttendanceSummary,
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
        if (!$this->isStudentScheduledForDate($student, $validated['attendance_date'])) {
            return redirect()->route('dashboard.attendances.index', [
                'lang' => app()->getLocale(),
                'date' => $validated['attendance_date'],
            ])->withErrors(['attendance_date' => 'Student is not scheduled for this day.']);
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

        $student = User::findOrFail($validated['student_id']);
        if (!$this->isStudentScheduledForDate($student, $validated['attendance_date'])) {
            return back()
                ->withErrors(['attendance_date' => 'Student is not scheduled for this day.'])
                ->withInput();
        }

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

        $student = User::findOrFail($validated['student_id']);
        if (!$this->isStudentScheduledForDate($student, $validated['attendance_date'])) {
            return back()
                ->withErrors(['attendance_date' => 'Student is not scheduled for this day.'])
                ->withInput();
        }

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

    private function isStudentScheduledForDate(User $student, string $attendanceDate): bool
    {
        $dayKey = strtolower(\Carbon\Carbon::parse($attendanceDate)->format('D'));
        $dayKey = substr($dayKey, 0, 3);
        $schedule = $student->schedule_days ?? [];

        if (empty($schedule)) {
            return true;
        }

        return in_array($dayKey, $schedule, true);
    }
}
