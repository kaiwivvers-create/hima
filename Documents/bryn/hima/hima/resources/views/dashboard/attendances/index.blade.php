@extends('dashboard.layout')

@section('title', 'Attendance')
@section('page_title', 'Attendance')

@section('content')
<div class="page-actions">
    <a class="btn" href="{{ route('dashboard.attendances.create', ['lang' => app()->getLocale()]) }}">Add Attendance</a>
</div>

<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Date</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->student?->name ?? '-' }}</td>
                    <td>{{ $attendance->attendance_date?->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($attendance->status) }}</td>
                    <td>{{ $attendance->notes ?: '-' }}</td>
                    <td>
                        <div class="actions">
                            <a class="btn-outline" href="{{ route('dashboard.attendances.edit', ['attendance' => $attendance, 'lang' => app()->getLocale()]) }}">Edit</a>
                            <form method="POST" action="{{ route('dashboard.attendances.destroy', ['attendance' => $attendance, 'lang' => app()->getLocale()]) }}" onsubmit="return confirm('Delete this attendance record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No attendance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $attendances->withQueryString()->links() }}</div>
</section>
@endsection
