<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        $parents = User::query()
            ->where('role', 'parent')
            ->orderBy('name')
            ->get();

        return view('dashboard.students.index', [
            'students' => Student::with('parentUser')->latest()->paginate(10),
            'parents' => $parents,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.students.create', [
            'parents' => User::query()->where('role', 'parent')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:50'],
            'parent_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'parent')),
            ],
        ]);

        $parent = User::findOrFail($validated['parent_user_id']);

        Student::create([
            'name' => $validated['name'],
            'class_name' => $validated['class_name'],
            'parent_user_id' => $parent->id,
            'parent_name' => $parent->name,
            'parent_contact' => $parent->email,
        ]);

        return redirect()->route('dashboard.students.index', ['lang' => app()->getLocale()])
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student): View
    {
        return view('dashboard.students.edit', [
            'student' => $student,
            'parents' => User::query()->where('role', 'parent')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'max:50'],
            'parent_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'parent')),
            ],
        ]);

        $parent = User::findOrFail($validated['parent_user_id']);

        $student->update([
            'name' => $validated['name'],
            'class_name' => $validated['class_name'],
            'parent_user_id' => $parent->id,
            'parent_name' => $parent->name,
            'parent_contact' => $parent->email,
        ]);

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
