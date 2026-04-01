@extends('dashboard.layout')

@section('title', 'Create Attendance')
@section('page_title', 'Create Attendance')

@section('content')
<section class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('dashboard.attendances.store', ['lang' => app()->getLocale()]) }}">
        @csrf

        <div class="field">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" required>
                <option value="">Select student</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->name }} ({{ $student->email }})</option>
                @endforeach
            </select>
            @error('student_id')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="attendance_date">Date</label>
            <input id="attendance_date" name="attendance_date" type="date" value="{{ old('attendance_date') }}" required>
            @error('attendance_date')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="present" @selected(old('status') === 'present')>{{ __('Present') }}</option>
                <option value="late" @selected(old('status') === 'late')>{{ __('Late') }}</option>
                <option value="absent" @selected(old('status') === 'absent')>{{ __('Absent') }}</option>
                <option value="excused" @selected(old('status') === 'excused')>{{ __('Excused') }}</option>
            </select>
            @error('status')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
            @error('notes')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn">Save</button>
            <a class="btn-outline" href="{{ route('dashboard.attendances.index', ['lang' => app()->getLocale()]) }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
