@extends('dashboard.layout')

@section('title', 'Absence Notes')
@section('page_title', 'Absence Notes')

@section('content')
<div class="page-actions">
    <button class="btn" type="button" data-modal-open="absence-create-modal">Add Absence</button>
</div>

<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Date</th>
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
                    <td>{{ $absence->absence_date?->format('Y-m-d') }}</td>
                    <td>{{ $absence->reason }}</td>
                    <td>{{ $absence->submitted_by ?: '-' }}</td>
                    <td>{{ ucfirst($absence->verification_status) }}</td>
                    <td>
                        <div class="actions">
                            <button class="btn-outline" type="button" data-modal-open="absence-edit-{{ $absence->id }}">Edit</button>
                            <button class="btn btn-danger" type="button" data-modal-open="absence-delete-{{ $absence->id }}">Delete</button>
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
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->class_name }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="create-absence-date">Absence Date</label>
                <input id="create-absence-date" name="absence_date" type="date" required>
            </div>
            <div class="field">
                <label for="create-reason">Reason</label>
                <textarea id="create-reason" name="reason" rows="3" required></textarea>
            </div>
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
            <div class="actions">
                <button type="submit" class="btn">Save</button>
            </div>
        </form>
    </div>
</div>

@foreach ($absences as $absence)
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
                            <option value="{{ $student->id }}" @selected((int) $absence->student_id === (int) $student->id)>{{ $student->name }} ({{ $student->class_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="edit-absence-date-{{ $absence->id }}">Absence Date</label>
                    <input id="edit-absence-date-{{ $absence->id }}" name="absence_date" type="date" value="{{ $absence->absence_date?->format('Y-m-d') }}" required>
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
@endforeach
@endsection
