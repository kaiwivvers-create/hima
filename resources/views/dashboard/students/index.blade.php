@php
    $dayLabels = [
        'mon' => 'Mon',
        'tue' => 'Tue',
        'wed' => 'Wed',
        'thu' => 'Thu',
        'fri' => 'Fri',
        'sat' => 'Sat',
        'sun' => 'Sun',
    ];
@endphp

@extends('dashboard.layout')

@section('title', 'Students')
@section('page_title', 'Students')

@section('content')
<div class="page-actions" style="justify-content:space-between; align-items:flex-end; flex-wrap:wrap;">
    <form method="GET" action="{{ route('dashboard.students.index', ['lang' => app()->getLocale()]) }}" class="actions" style="align-items:end; justify-content:flex-start;">
        <div class="field" style="margin:0; min-width:220px;">
            <label for="search">Search</label>
            <input id="search" name="search" type="text" value="{{ $search ?? '' }}" placeholder="Name or email">
        </div>
        <button type="submit" class="btn-outline" style="padding:.52rem .8rem;">Search</button>
    </form>
    @perm('students.create')
        <button class="btn" type="button" data-modal-open="student-create-modal" style="padding:.52rem .8rem;">Add Student</button>
    @endperm
</div>

<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Schedule</th>
                <th>Program</th>
                <th>Tuition (per year)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                @php
                    $schedule = $student->schedule_days ?? [];
                    $scheduleLabel = empty($schedule)
                        ? 'All days'
                        : collect($schedule)->map(fn ($day) => $dayLabels[$day] ?? strtoupper($day))->join(', ');
                    $programNames = $student->tuitionPrograms->pluck('name')->join(', ') ?: '-';
                    $totalAnnual = (float) $student->tuitionPrograms->sum(fn ($p) => (float) $p->pivot->annual_amount);
                @endphp
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $scheduleLabel }}</td>
                    <td>{{ $programNames }}</td>
                    <td>{{ $totalAnnual > 0 ? number_format($totalAnnual, 2) : '-' }}</td>
                    <td>
                        <div class="actions">
                            @perm('students.update')
                                <button class="btn-outline" type="button" data-modal-open="student-edit-{{ $student->id }}">Edit</button>
                            @endperm
                            @perm('students.delete')
                                <button class="btn btn-danger" type="button" data-modal-open="student-delete-{{ $student->id }}">Delete</button>
                            @endperm
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $students->links() }}</div>
</section>@perm('students.create')
<div class="modal" id="student-create-modal">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card">
        <div class="modal-head">
            <h2>Add Student</h2>
            <button class="btn-outline" type="button" data-modal-close>Close</button>
        </div>
        <form method="POST" action="{{ route('dashboard.students.store', ['lang' => app()->getLocale()]) }}">
            @csrf
            <div class="field">
                <label for="create-name">Name</label>
                <input id="create-name" name="name" type="text" required>
            </div>
            <div class="field">
                <label for="create-email">Email</label>
                <input id="create-email" name="email" type="email" required>
            </div>
            <div class="field">
                <label for="create-password">Password</label>
                <input id="create-password" name="password" type="password" minlength="6" required>
            </div>
            <div class="field">
                <label>Schedule Days</label>
                <div class="actions">
                    @foreach ($days as $day)
                        <label><input type="checkbox" name="schedule_days[]" value="{{ $day }}" checked> {{ $dayLabels[$day] ?? strtoupper($day) }}</label>
                    @endforeach
                </div>
            </div>
            <div class="field">
                <label>Tuition Programs</label>
                <p class="muted" style="font-size:.85rem;margin:.1rem 0 .5rem;">Check the programs the student follows and set the annual tuition for each.</p>
                @foreach ($tuitionPrograms as $slug => $program)
                    <div style="display:flex;gap:.6rem;align-items:center;margin-bottom:.45rem;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:.4rem;min-width:200px;font-weight:600;">
                            <input type="checkbox" name="programs[]" value="{{ $slug }}" data-annual="{{ round((float) $program->monthly_amount * 12, 2) }}">
                            {{ $program->name }}
                        </label>
                        <input type="number" name="annual[{{ $slug }}]" step="0.01" min="0" style="max-width:220px;flex:1;" placeholder="Annual amount">
                    </div>
                @endforeach
            </div>
            <div class="actions">
                <button type="submit" class="btn">Save</button>
            </div>
        </form>
    </div>
</div>
@endperm

@foreach ($students as $student)
    @php
        $selectedDays = !empty($student->schedule_days) ? $student->schedule_days : $days;
        $selectedPrograms = $student->tuitionPrograms->keyBy('slug');
    @endphp
    @perm('students.update')
    <div class="modal" id="student-edit-{{ $student->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Edit Student</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <form method="POST" action="{{ route('dashboard.students.update', ['student' => $student, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="edit-name-{{ $student->id }}">Name</label>
                    <input id="edit-name-{{ $student->id }}" name="name" type="text" value="{{ $student->name }}" required>
                </div>
                <div class="field">
                    <label for="edit-email-{{ $student->id }}">Email</label>
                    <input id="edit-email-{{ $student->id }}" name="email" type="email" value="{{ $student->email }}" required>
                </div>
                <div class="field">
                    <label for="edit-password-{{ $student->id }}">Password (leave blank to keep)</label>
                    <input id="edit-password-{{ $student->id }}" name="password" type="password" minlength="6">
                </div>
                <div class="field">
                    <label>Schedule Days</label>
                    <div class="actions">
                        @foreach ($days as $day)
                            <label>
                                <input type="checkbox" name="schedule_days[]" value="{{ $day }}" @checked(in_array($day, $selectedDays, true))>
                                {{ $dayLabels[$day] ?? strtoupper($day) }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="field">
                    <label>Tuition Programs</label>
                    <p class="muted" style="font-size:.85rem;margin:.1rem 0 .5rem;">Check the programs the student follows and set the annual tuition for each.</p>
                    @foreach ($tuitionPrograms as $slug => $program)
                        @php
                            $enrolled = $selectedPrograms->get($slug);
                        @endphp
                        <div style="display:flex;gap:.6rem;align-items:center;margin-bottom:.45rem;flex-wrap:wrap;">
                            <label style="display:flex;align-items:center;gap:.4rem;min-width:200px;font-weight:600;">
                                <input type="checkbox" name="programs[]" value="{{ $slug }}" data-annual="{{ round((float) $program->monthly_amount * 12, 2) }}" @checked($enrolled !== null)>
                                {{ $program->name }}
                            </label>
                            <input type="number" name="annual[{{ $slug }}]" step="0.01" min="0" style="max-width:220px;flex:1;" placeholder="Annual amount" value="{{ $enrolled ? $enrolled->pivot->annual_amount : '' }}">
                        </div>
                    @endforeach
                </div>
                <div class="actions">
                    <button type="submit" class="btn">Update</button>
                </div>
            </form>
        </div>
    </div>
    @endperm

    @perm('students.delete')
    <div class="modal" id="student-delete-{{ $student->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Delete Student</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <p>Delete <strong>{{ $student->name }}</strong>? This cannot be undone.</p>
            <form method="POST" action="{{ route('dashboard.students.destroy', ['student' => $student, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('DELETE')
                <div class="actions">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    @endperm
@endforeach
<script>
    (function () {
        document.querySelectorAll('[name="programs[]"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (!checkbox.checked) return;
                const annual = checkbox.dataset.annual;
                const form = checkbox.closest('form');
                const input = form && form.querySelector('input[name="annual[' + checkbox.value + ']"]');
                if (input && annual && !input.value) {
                    input.value = annual;
                }
            });
        });
    })();
</script>
@endsection
