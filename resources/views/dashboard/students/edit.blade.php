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
    $selectedPrograms = $student->tuitionPrograms->keyBy('slug');
@endphp

@extends('dashboard.layout')

@section('title', 'Edit Student')
@section('page_title', 'Edit Student')

@section('content')
<section class="card" style="max-width:640px;">
    <form method="POST" action="{{ route('dashboard.students.update', ['student' => $student, 'lang' => app()->getLocale()]) }}">
        @csrf
        @method('PUT')
        <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $student->name) }}" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $student->email) }}" required>
        </div>
        <div class="field">
            <label for="password">Password (leave blank to keep)</label>
            <input id="password" name="password" type="password" minlength="6">
        </div>
        <div class="field">
            <label>Schedule Days</label>
            <div class="actions">
                @foreach ($days as $day)
                    <label><input type="checkbox" name="schedule_days[]" value="{{ $day }}" @checked(in_array($day, old('schedule_days', !empty($student->schedule_days) ? $student->schedule_days : $days), true))> {{ $dayLabels[$day] ?? strtoupper($day) }}</label>
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
</section>

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
