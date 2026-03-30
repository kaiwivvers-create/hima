<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        return view('dashboard.students.index', [
            'students' => Student::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.students.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:50'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_contact' => ['required', 'string', 'max:50'],
        ]);

        Student::create($validated);

        return redirect()->route('dashboard.students.index', ['lang' => app()->getLocale()])
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student): View
    {
        return view('dashboard.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:50'],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_contact' => ['required', 'string', 'max:50'],
        ]);

        $student->update($validated);

        return redirect()->route('dashboard.students.index', ['lang' => app()->getLocale()])
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('dashboard.students.index', ['lang' => app()->getLocale()])
            ->with('success', 'Student deleted successfully.');
    }
}
