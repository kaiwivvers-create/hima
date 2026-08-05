<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\TuitionProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TuitionProgramController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if(in_array($user->role, ['student', 'parent'], true), 403);

        return view('dashboard.admin.tuition-programs.index', [
            'programs' => TuitionProgram::query()->orderBy('name')->get(),
            'plans' => TuitionProgram::PLANS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if(in_array($user->role, ['student', 'parent'], true), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:tuition_programs,slug'],
            'monthly_amount' => ['required', 'numeric', 'min:0'],
            'bi_monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'triannual_amount' => ['nullable', 'numeric', 'min:0'],
            'quarterly_amount' => ['nullable', 'numeric', 'min:0'],
            'yearly_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        TuitionProgram::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'monthly_amount' => $validated['monthly_amount'],
            'bi_monthly_amount' => $validated['bi_monthly_amount'] ?? null,
            'triannual_amount' => $validated['triannual_amount'] ?? null,
            'quarterly_amount' => $validated['quarterly_amount'] ?? null,
            'yearly_amount' => $validated['yearly_amount'] ?? null,
        ]);

        return back()->with('success', 'Tuition program created.');
    }

    public function update(Request $request, TuitionProgram $program): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if(in_array($user->role, ['student', 'parent'], true), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('tuition_programs', 'slug')->ignore($program->id),
            ],
            'monthly_amount' => ['required', 'numeric', 'min:0'],
            'bi_monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'triannual_amount' => ['nullable', 'numeric', 'min:0'],
            'quarterly_amount' => ['nullable', 'numeric', 'min:0'],
            'yearly_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $program->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'monthly_amount' => $validated['monthly_amount'],
            'bi_monthly_amount' => $validated['bi_monthly_amount'] ?? null,
            'triannual_amount' => $validated['triannual_amount'] ?? null,
            'quarterly_amount' => $validated['quarterly_amount'] ?? null,
            'yearly_amount' => $validated['yearly_amount'] ?? null,
        ]);

        return back()->with('success', 'Tuition program updated.');
    }

    public function destroy(Request $request, TuitionProgram $program): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if(in_array($user->role, ['student', 'parent'], true), 403);

        $inUse = \App\Models\User::query()
            ->where('role', 'student')
            ->where('tuition_program', $program->slug)
            ->exists();
        if ($inUse) {
            return back()->withErrors([
                'tuition_program' => 'Cannot delete program that is assigned to students.',
            ]);
        }

        $inPivot = \Illuminate\Support\Facades\DB::table('student_tuition_program')
            ->where('tuition_program_id', $program->id)
            ->exists();
        if ($inPivot) {
            return back()->withErrors([
                'tuition_program' => 'Cannot delete program that is assigned to students.',
            ]);
        }

        $program->delete();

        return back()->with('success', 'Tuition program deleted.');
    }
}
