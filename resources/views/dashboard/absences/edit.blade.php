@extends('dashboard.layout')

@section('title', 'Edit Absence')
@section('page_title', 'Edit Absence')

@section('content')
<section class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('dashboard.absences.update', ['absence' => $absence, 'lang' => app()->getLocale()]) }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" required>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) old('student_id', $absence->student_id) === (string) $student->id)>{{ $student->name }} ({{ $student->email }})</option>
                @endforeach
            </select>
            @error('student_id')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="start_date">Start Date</label>
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $absence->start_date?->format('Y-m-d')) }}" required>
            @error('start_date')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="end_date">End Date</label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $absence->end_date?->format('Y-m-d')) }}" required>
            @error('end_date')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="reason">Reason</label>
            <textarea id="reason" name="reason" rows="3" required>{{ old('reason', $absence->reason) }}</textarea>
            @error('reason')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="submitted_by">Submitted By (optional)</label>
            <input id="submitted_by" name="submitted_by" type="text" value="{{ old('submitted_by', $absence->submitted_by) }}">
            @error('submitted_by')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="verification_status">Verification Status</label>
            <select id="verification_status" name="verification_status" required>
                <option value="pending" @selected(old('verification_status', $absence->verification_status) === 'pending')>Pending</option>
                <option value="approved" @selected(old('verification_status', $absence->verification_status) === 'approved')>Approved</option>
                <option value="rejected" @selected(old('verification_status', $absence->verification_status) === 'rejected')>Rejected</option>
            </select>
            @error('verification_status')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn">Update</button>
            <a class="btn-outline" href="{{ route('dashboard.absences.index', ['lang' => app()->getLocale()]) }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
