<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $attendanceDate = $request->query('date', now()->toDateString());
        $students = Student::orderBy('name')->get();

        $records = Attendance::whereDate('attendance_date', $attendanceDate)
            ->get()
            ->keyBy('student_id');

        return view('dashboard.attendances.index', [
            'students' => $students,
            'attendanceDate' => $attendanceDate,
            'records' => $records,
        ]);
    }

    public function mark(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['present', 'sick', 'izin', 'absent'])],
            'notes' => ['nullable', 'string'],
        ]);

        Attendance::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'attendance_date' => $validated['attendance_date'],
            ],
            [
                'status' => $this->toDatabaseStatus($validated['status']),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->route('dashboard.attendances.index', [
            'lang' => app()->getLocale(),
            'date' => $validated['attendance_date'],
        ])->with('success', 'Attendance saved.');
    }

    public function create(): View
    {
        return view('dashboard.attendances.create', [
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
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

        Attendance::create($validated);

        return redirect()->route('dashboard.attendances.index', ['lang' => app()->getLocale()])
            ->with('success', 'Attendance record created successfully.');
    }

    public function edit(Attendance $attendance): View
    {
        return view('dashboard.attendances.edit', [
            'attendance' => $attendance,
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
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

        $attendance->update($validated);

        return redirect()->route('dashboard.attendances.index', ['lang' => app()->getLocale()])
            ->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->route('dashboard.attendances.index', ['lang' => app()->getLocale()])
            ->with('success', 'Attendance record deleted successfully.');
    }

    private function toDatabaseStatus(string $status): string
    {
        return match ($status) {
            'sick' => 'late',
            'izin' => 'excused',
            default => $status,
        };
    }
}
