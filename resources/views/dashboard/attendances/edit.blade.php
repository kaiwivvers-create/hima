@extends('dashboard.layout')

@section('title', 'Edit Attendance')
@section('page_title', 'Edit Attendance')

@section('content')
<section class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('dashboard.attendances.update', ['attendance' => $attendance, 'lang' => app()->getLocale()]) }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" required>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) old('student_id', $attendance->student_id) === (string) $student->id)>{{ $student->name }} ({{ $student->email }})</option>
                @endforeach
            </select>
            @error('student_id')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="attendance_date">Date</label>
            <input id="attendance_date" name="attendance_date" type="date" value="{{ old('attendance_date', $attendance->attendance_date?->format('Y-m-d')) }}" required>
            @error('attendance_date')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="present" @selected(old('status', $attendance->status) === 'present')>Present</option>
                <option value="late" @selected(old('status', $attendance->status) === 'late')>Late</option>
                <option value="absent" @selected(old('status', $attendance->status) === 'absent')>Absent</option>
                <option value="excused" @selected(old('status', $attendance->status) === 'excused')>Excused</option>
            </select>
            @error('status')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3">{{ old('notes', $attendance->notes) }}</textarea>
            @error('notes')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn">Update</button>
            <a class="btn-outline" href="{{ route('dashboard.attendances.index', ['lang' => app()->getLocale()]) }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
