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
    public function index(): View
    {
        return view('dashboard.attendances.index', [
            'attendances' => Attendance::with('student')->latest('attendance_date')->paginate(10),
        ]);
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
}
