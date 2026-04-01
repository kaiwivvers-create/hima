<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TuitionProgram;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:students.view')->only(['index']);
        $this->middleware('permission:students.create')->only(['create', 'store']);
        $this->middleware('permission:students.update')->only(['edit', 'update']);
        $this->middleware('permission:students.delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $query = User::where('role', 'student')->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        return view('dashboard.students.index', [
            'students' => $query->paginate(10)->withQueryString(),
            'days' => $this->allowedDays(),
            'search' => $search,
            'tuitionPrograms' => $this->tuitionPrograms(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.students.create', [
            'days' => $this->allowedDays(),
            'tuitionPrograms' => $this->tuitionPrograms(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedDays = $this->allowedDays();
        $programs = $this->tuitionPrograms();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'schedule_days' => ['required', 'array', 'min:1'],
            'schedule_days.*' => ['string', Rule::in($allowedDays)],
            'tuition_amount' => ['nullable', 'numeric', 'min:0'],
            'tuition_program' => ['nullable', Rule::in(array_keys($programs))],
        ]);

        if (empty($validated['tuition_amount']) && !empty($validated['tuition_program'])) {
            $validated['tuition_amount'] = $this->defaultAnnualTuition($validated['tuition_program']);
        }

        $student = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'student',
            'schedule_days' => $validated['schedule_days'],
            'tuition_amount' => $validated['tuition_amount'] ?? null,
            'tuition_program' => $validated['tuition_program'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLogger::log(
            'user.created',
            'user',
            $student->id,
            'Student user created: '.$student->email,
            null,
            ActivityLogger::snapshot($student, 'user')
        );

        return redirect()->route('dashboard.students.index', ['lang' => app()->getLocale()])
            ->with('success', 'Student created successfully.');
    }

    public function edit(User $student): View
    {
        return view('dashboard.students.edit', [
            'student' => $student,
            'days' => $this->allowedDays(),
            'tuitionPrograms' => $this->tuitionPrograms(),
        ]);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        $allowedDays = $this->allowedDays();
        $programs = $this->tuitionPrograms();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'schedule_days' => ['required', 'array', 'min:1'],
            'schedule_days.*' => ['string', Rule::in($allowedDays)],
            'tuition_amount' => ['nullable', 'numeric', 'min:0'],
            'tuition_program' => ['nullable', Rule::in(array_keys($programs))],
        ]);

        if (empty($validated['tuition_amount']) && !empty($validated['tuition_program'])) {
            $validated['tuition_amount'] = $this->defaultAnnualTuition($validated['tuition_program']);
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'student',
            'schedule_days' => $validated['schedule_days'],
            'tuition_amount' => $validated['tuition_amount'] ?? null,
            'tuition_program' => $validated['tuition_program'] ?? null,
        ];

        $before = ActivityLogger::snapshot($student, 'user');

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $student->update($payload);

        if (!empty($validated['password'])) {
            ActivityLogger::log(
                'user.password_changed',
                'user',
                $student->id,
                'Password changed for: '.$student->email,
                null,
                null
            );
        }

        ActivityLogger::log(
            'user.updated',
            'user',
            $student->id,
            'Student user updated: '.$student->email,
            $before,
            ActivityLogger::snapshot($student, 'user')
        );

        return redirect()->route('dashboard.students.index', ['lang' => app()->getLocale()])
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(User $student): RedirectResponse
    {
        $before = ActivityLogger::snapshot($student, 'user');
        $student->delete();

        ActivityLogger::log(
            'user.deleted',
            'user',
            $student->id,
            'Student user deleted: '.$student->email,
            $before,
            null
        );

        return redirect()->route('dashboard.students.index', ['lang' => app()->getLocale()])
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * @return array<int, string>
     */
    private function allowedDays(): array
    {
        return ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    }

    /**
     * @return array<string, array{label:string, monthly:int}>
     */
    private function tuitionPrograms(): array
    {
        $programs = TuitionProgram::query()
            ->orderBy('name')
            ->get(['slug', 'name', 'monthly_amount']);

        if ($programs->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($programs as $program) {
            $result[$program->slug] = [
                'label' => $program->name,
                'monthly' => (float) $program->monthly_amount,
            ];
        }

        return $result;
    }

    private function defaultAnnualTuition(string $program): ?float
    {
        $programs = $this->tuitionPrograms();
        if (!isset($programs[$program])) {
            return null;
        }

        return $programs[$program]['monthly'] * 12;
    }
}
