@extends('dashboard.layout')

@section('title', 'Absence Notes')
@section('page_title', 'Absence Notes')

@section('content')
@php
    $canSubmitAbsence = auth()->user()?->can('absences.create') || in_array(auth()->user()?->role, ['parent', 'student'], true);
@endphp
<div class="page-actions">
    @if ($canSubmitAbsence)
        <button class="btn" type="button" data-modal-open="absence-create-modal">Add Absence</button>
    @endif
</div>

<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Dates</th>
                <th>Reason</th>
                <th>Submitted By</th>
                <th>Verification</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($absences as $absence)
                <tr>
                    <td>{{ $absence->student?->name ?? '-' }}</td>
                    <td>
                        @if ($absence->start_date && $absence->end_date)
                            {{ $absence->start_date->format('Y-m-d') }} &ndash; {{ $absence->end_date->format('Y-m-d') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $absence->reason }}</td>
                    <td>{{ $absence->submitted_by ?: '-' }}</td>
                    <td>{{ ucfirst($absence->verification_status) }}</td>
                    <td>
                        <div class="actions">
                            @perm('absences.update')
                                @if ($absence->verification_status === 'pending')
                                    <form method="POST" action="{{ route('dashboard.absences.approve', ['absence' => $absence, 'lang' => app()->getLocale()]) }}">
                                        @csrf
                                        <button class="btn" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.absences.reject', ['absence' => $absence, 'lang' => app()->getLocale()]) }}">
                                        @csrf
                                        <button class="btn btn-danger" type="submit">Reject</button>
                                    </form>
                                @else
                                    <button class="btn-outline" type="button" data-modal-open="absence-edit-{{ $absence->id }}">Edit</button>
                                @endif
                            @endperm
                            @perm('absences.delete')
                                @if ($absence->verification_status !== 'pending')
                                <button class="btn btn-danger" type="button" data-modal-open="absence-delete-{{ $absence->id }}">Delete</button>
                                @endif
                            @endperm
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">No absence records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $absences->withQueryString()->links() }}</div>
</section>

@perm('absences.create')
<div class="modal" id="absence-create-modal">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card">
        <div class="modal-head">
            <h2>Add Absence</h2>
            <button class="btn-outline" type="button" data-modal-close>Close</button>
        </div>
        <form method="POST" action="{{ route('dashboard.absences.store', ['lang' => app()->getLocale()]) }}">
            @csrf
            <div class="field">
                <label for="create-absence-student">Student</label>
                <select id="create-absence-student" name="student_id" required>
                    <option value="">Select student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="create-start-date">Start Date</label>
                <input id="create-start-date" name="start_date" type="date" required>
            </div>
            <div class="field">
                <label for="create-end-date">End Date</label>
                <input id="create-end-date" name="end_date" type="date" required>
            </div>
            <div class="field">
                <label for="create-reason">Reason</label>
                <textarea id="create-reason" name="reason" rows="3" required></textarea>
            </div>
            @can('absences.create')
                <div class="field">
                    <label for="create-submitted-by">Submitted By</label>
                    <input id="create-submitted-by" name="submitted_by" type="text">
                </div>
                <div class="field">
                    <label for="create-verification-status">Verification Status</label>
                    <select id="create-verification-status" name="verification_status" required>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            @else
                <input type="hidden" name="verification_status" value="pending">
            @endcan
            <div class="actions">
                <button type="submit" class="btn">Save</button>
            </div>
        </form>
    </div>
</div>
@endperm

@foreach ($absences as $absence)
    @perm('absences.update')
    <div class="modal" id="absence-edit-{{ $absence->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Edit Absence</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <form method="POST" action="{{ route('dashboard.absences.update', ['absence' => $absence, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="edit-absence-student-{{ $absence->id }}">Student</label>
                    <select id="edit-absence-student-{{ $absence->id }}" name="student_id" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected((int) $absence->student_id === (int) $student->id)>{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="edit-start-date-{{ $absence->id }}">Start Date</label>
                    <input id="edit-start-date-{{ $absence->id }}" name="start_date" type="date" value="{{ old('start_date', $absence->start_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="field">
                    <label for="edit-end-date-{{ $absence->id }}">End Date</label>
                    <input id="edit-end-date-{{ $absence->id }}" name="end_date" type="date" value="{{ old('end_date', $absence->end_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="field">
                    <label for="edit-reason-{{ $absence->id }}">Reason</label>
                    <textarea id="edit-reason-{{ $absence->id }}" name="reason" rows="3" required>{{ $absence->reason }}</textarea>
                </div>
                <div class="field">
                    <label for="edit-submitted-by-{{ $absence->id }}">Submitted By</label>
                    <input id="edit-submitted-by-{{ $absence->id }}" name="submitted_by" type="text" value="{{ $absence->submitted_by }}">
                </div>
                <div class="field">
                    <label for="edit-verification-status-{{ $absence->id }}">Verification Status</label>
                    <select id="edit-verification-status-{{ $absence->id }}" name="verification_status" required>
                        <option value="pending" @selected($absence->verification_status === 'pending')>Pending</option>
                        <option value="approved" @selected($absence->verification_status === 'approved')>Approved</option>
                        <option value="rejected" @selected($absence->verification_status === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div class="actions">
                    <button type="submit" class="btn">Update</button>
                </div>
            </form>
        </div>
    </div>
    @endperm

    @perm('absences.delete')
    <div class="modal" id="absence-delete-{{ $absence->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Delete Absence</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
                        <p>Delete absence record for <strong>{{ $absence->student?->name ?? '-' }}</strong>?</p>
            <form method="POST" action="{{ route('dashboard.absences.destroy', ['absence' => $absence, 'lang' => app()->getLocale()]) }}">
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
@endsection
