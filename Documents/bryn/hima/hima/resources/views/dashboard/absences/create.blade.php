@extends('dashboard.layout')

@section('title', 'Create Absence')
@section('page_title', 'Create Absence')

@section('content')
<section class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('dashboard.absences.store', ['lang' => app()->getLocale()]) }}">
        @csrf

        <div class="field">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" required>
                <option value="">Select student</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->name }} ({{ $student->class_name }})</option>
                @endforeach
            </select>
            @error('student_id')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="absence_date">Absence Date</label>
            <input id="absence_date" name="absence_date" type="date" value="{{ old('absence_date') }}" required>
            @error('absence_date')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="reason">Reason</label>
            <textarea id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
            @error('reason')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="submitted_by">Submitted By (optional)</label>
            <input id="submitted_by" name="submitted_by" type="text" value="{{ old('submitted_by') }}">
            @error('submitted_by')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="verification_status">Verification Status</label>
            <select id="verification_status" name="verification_status" required>
                <option value="pending" @selected(old('verification_status') === 'pending')>Pending</option>
                <option value="approved" @selected(old('verification_status') === 'approved')>Approved</option>
                <option value="rejected" @selected(old('verification_status') === 'rejected')>Rejected</option>
            </select>
            @error('verification_status')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn">Save</button>
            <a class="btn-outline" href="{{ route('dashboard.absences.index', ['lang' => app()->getLocale()]) }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
