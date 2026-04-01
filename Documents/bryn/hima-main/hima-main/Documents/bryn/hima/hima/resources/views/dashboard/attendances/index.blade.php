@extends('dashboard.layout')

@section('title', 'Attendance')
@section('page_title', 'Attendance')

@section('content')
<section class="card" style="margin-bottom:.85rem;">
    <form method="GET" action="{{ route('dashboard.attendances.index') }}" class="actions" style="align-items:end;">
        <input type="hidden" name="lang" value="{{ app()->getLocale() }}">
        <div class="field" style="margin:0;max-width:230px;">
            <label for="date">Attendance Date</label>
            <input id="date" type="date" name="date" value="{{ $attendanceDate }}">
        </div>
        <button type="submit" class="btn">Load</button>
    </form>
</section>

<div class="grid">
    @forelse ($students as $student)
        @php
            $record = $records->get($student->id);
            $status = match($record?->status) {
                'late' => 'sick',
                'excused' => 'izin',
                default => $record?->status ?? 'present',
            };
        @endphp

        <section class="card" style="grid-column: span 6;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.8rem;margin-bottom:.55rem;">
                <div>
                    <h3 style="margin:0 0 .2rem;font-size:1.05rem;">{{ $student->name }}</h3>
                    <p class="muted" style="margin:0;">Class {{ $student->class_name }}</p>
                </div>
                <span class="btn-outline" style="cursor:default;">{{ strtoupper($status) }}</span>
            </div>

            <form method="POST" action="{{ route('dashboard.attendances.mark', ['lang' => app()->getLocale()]) }}">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

                <div class="field">
                    <label>Status</label>
                    <div class="actions">
                        <label><input type="radio" name="status" value="present" @checked($status === 'present')> Present</label>
                        <label><input type="radio" name="status" value="sick" @checked($status === 'sick')> Sick</label>
                        <label><input type="radio" name="status" value="izin" @checked($status === 'izin')> Izin</label>
                        <label><input type="radio" name="status" value="absent" @checked($status === 'absent')> Absent</label>
                    </div>
                </div>

                <div class="field" style="margin-bottom:.5rem;">
                    <label for="notes-{{ $student->id }}">Notes</label>
                    <textarea id="notes-{{ $student->id }}" name="notes" rows="2">{{ $record?->notes }}</textarea>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Save</button>
                </div>
            </form>
        </section>
    @empty
        <section class="card" style="grid-column: span 12;">
            <p class="muted" style="margin:0;">No students found. Add students first to mark attendance.</p>
        </section>
    @endforelse
</div>
@endsection
