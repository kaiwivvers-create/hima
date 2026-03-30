@extends('dashboard.layout')

@section('title', 'Absence Notes')
@section('page_title', 'Absence Notes')

@section('content')
<div class="page-actions">
    <a class="btn" href="{{ route('dashboard.absences.create', ['lang' => app()->getLocale()]) }}">Add Absence</a>
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
                            <a class="btn-outline" href="{{ route('dashboard.absences.edit', ['absence' => $absence, 'lang' => app()->getLocale()]) }}">Edit</a>
                            <form method="POST" action="{{ route('dashboard.absences.destroy', ['absence' => $absence, 'lang' => app()->getLocale()]) }}" onsubmit="return confirm('Delete this absence record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
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
@endsection
