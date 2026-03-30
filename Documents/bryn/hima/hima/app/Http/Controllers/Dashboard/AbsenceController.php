<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AbsenceController extends Controller
{
    public function index(): View
    {
        return view('dashboard.absences.index', [
            'absences' => Absence::with('student')->latest('absence_date')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.absences.create', [
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'absence_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'verification_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'submitted_by' => ['nullable', 'string', 'max:255'],
        ]);

        Absence::create($validated);

        return redirect()->route('dashboard.absences.index', ['lang' => app()->getLocale()])
            ->with('success', 'Absence record created successfully.');
    }

    public function edit(Absence $absence): View
    {
        return view('dashboard.absences.edit', [
            'absence' => $absence,
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Absence $absence): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'absence_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'verification_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'submitted_by' => ['nullable', 'string', 'max:255'],
        ]);

        $absence->update($validated);

        return redirect()->route('dashboard.absences.index', ['lang' => app()->getLocale()])
            ->with('success', 'Absence record updated successfully.');
    }

    public function destroy(Absence $absence): RedirectResponse
    {
        $absence->delete();

        return redirect()->route('dashboard.absences.index', ['lang' => app()->getLocale()])
            ->with('success', 'Absence record deleted successfully.');
    }
}
