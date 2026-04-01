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
            <label for="tuition_program">Tuition Program</label>
            <select id="tuition_program" name="tuition_program" class="js-tuition-program" data-target="tuition_amount">
                <option value="">Select program</option>
                @foreach ($tuitionPrograms as $key => $program)
                    <option value="{{ $key }}" data-annual="{{ $program['monthly'] * 12 }}" @selected($student->tuition_program === $key)>{{ $program['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="tuition_amount">Tuition Amount (per year)</label>
            <input id="tuition_amount" name="tuition_amount" type="number" min="0" step="0.01" value="{{ old('tuition_amount', $student->tuition_amount) }}">
            <div class="muted" style="font-size:.8rem; margin-top:.2rem;">
                <label><input type="checkbox" class="tuition-override" data-target="tuition_amount"> Allow manual edit</label>
            </div>
        </div>
        <div class="actions">
            <button type="submit" class="btn">Update</button>
        </div>
    </form>
</section>

<script>
    (function () {
        function toggleOverride(checkbox, target) {
            if (!checkbox || !target) return;
            target.readOnly = !checkbox.checked;
            target.classList.toggle('muted', target.readOnly);
        }

        document.querySelectorAll('.js-tuition-program').forEach(function (select) {
            select.addEventListener('change', function () {
                const selected = select.options[select.selectedIndex];
                const annual = selected ? selected.dataset.annual : null;
                const targetId = select.dataset.target;
                const target = document.getElementById(targetId);
                const overrideId = select.dataset.override;
                const override = overrideId ? document.getElementById(overrideId) : null;
                if (!target || !annual) return;
                if (!target.value || target.readOnly) {
                    target.value = annual;
                }
                if (override) {
                    override.checked = false;
                    toggleOverride(override, target);
                }
            });
        });

        document.querySelectorAll('.tuition-override').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const target = document.getElementById(checkbox.dataset.target);
                toggleOverride(checkbox, target);
            });
        });
    })();
</script>
@endsection
