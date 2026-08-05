<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TuitionProgram;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $query = User::where('role', 'student')
            ->with('tuitionPrograms')
            ->latest();

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
            'programs' => ['nullable', 'array'],
            'programs.*' => ['string', Rule::in(array_keys($programs))],
            'annual' => ['nullable', 'array'],
            'annual.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $student = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'student',
            'schedule_days' => $validated['schedule_days'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->syncPrograms($student, $validated['programs'] ?? [], $validated['annual'] ?? [], $programs);

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
            'student' => $student->load('tuitionPrograms'),
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
            'programs' => ['nullable', 'array'],
            'programs.*' => ['string', Rule::in(array_keys($programs))],
            'annual' => ['nullable', 'array'],
            'annual.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'student',
            'schedule_days' => $validated['schedule_days'],
        ];

        $before = ActivityLogger::snapshot($student, 'user');

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $student->update($payload);
        $this->syncPrograms($student, $validated['programs'] ?? [], $validated['annual'] ?? [], $programs);

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
     * @return array<string, TuitionProgram>
     */
    private function tuitionPrograms(): array
    {
        $programs = TuitionProgram::query()
            ->orderBy('name')
            ->get();

        $result = [];
        foreach ($programs as $program) {
            $result[$program->slug] = $program;
        }

        return $result;
    }

    /**
     * Sync the student's enrolled tuition programs (multi-program support).
     *
     * @param array<int, string> $selectedSlugs
     * @param array<string, mixed> $annualAmounts
     * @param array<string, TuitionProgram> $programs
     */
    private function syncPrograms(User $student, array $selectedSlugs, array $annualAmounts, array $programs): void
    {
        $pivot = [];
        foreach ($selectedSlugs as $slug) {
            if (!isset($programs[$slug])) {
                continue;
            }

            $annual = isset($annualAmounts[$slug]) && $annualAmounts[$slug] !== '' && $annualAmounts[$slug] !== null
                ? (float) $annualAmounts[$slug]
                : round((float) $programs[$slug]->monthly_amount * 12, 2);

            $pivot[$programs[$slug]->id] = ['annual_amount' => $annual > 0 ? $annual : null];
        }

        $student->tuitionPrograms()->sync($pivot);
    }
}
