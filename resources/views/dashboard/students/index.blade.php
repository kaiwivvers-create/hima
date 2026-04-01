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
                <th>Tuition</th>
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
                    $programLabel = $tuitionPrograms[$student->tuition_program]['label'] ?? '-';
                @endphp
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $scheduleLabel }}</td>
                    <td>{{ $programLabel }}</td>
                    <td>{{ $student->tuition_amount !== null ? number_format((float) $student->tuition_amount, 2) : '-' }}</td>
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
</section>

@perm('students.create')
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
                <label for="create-program">Tuition Program</label>
                <select id="create-program" name="tuition_program" class="js-tuition-program" data-target="create-tuition">
                    <option value="">Select program</option>
                    @foreach ($tuitionPrograms as $key => $program)
                        <option value="{{ $key }}" data-annual="{{ $program['monthly'] * 12 }}">{{ $program['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="create-tuition">Tuition Amount (per year)</label>
                <input id="create-tuition" name="tuition_amount" type="number" min="0" step="0.01">
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
                    <label for="edit-program-{{ $student->id }}">Tuition Program</label>
                    <select id="edit-program-{{ $student->id }}" name="tuition_program" class="js-tuition-program" data-target="edit-tuition-{{ $student->id }}">
                        <option value="">Select program</option>
                        @foreach ($tuitionPrograms as $key => $program)
                            <option value="{{ $key }}" data-annual="{{ $program['monthly'] * 12 }}" @selected($student->tuition_program === $key)>{{ $program['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="edit-tuition-{{ $student->id }}">Tuition Amount (per year)</label>
                    <input id="edit-tuition-{{ $student->id }}" name="tuition_amount" type="number" min="0" step="0.01" value="{{ $student->tuition_amount }}">
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
        document.querySelectorAll('.js-tuition-program').forEach(function (select) {
            select.addEventListener('change', function () {
                const selected = select.options[select.selectedIndex];
                const annual = selected ? selected.dataset.annual : null;
                const targetId = select.dataset.target;
                const target = document.getElementById(targetId);
                if (!target || !annual) return;
                if (!target.value) {
                    target.value = annual;
                }
            });
        });
    })();
</script>
@endsection
