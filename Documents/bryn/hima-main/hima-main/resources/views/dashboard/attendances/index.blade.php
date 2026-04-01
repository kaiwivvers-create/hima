@extends('dashboard.layout')

@section('title', 'Attendance')
@section('page_title', 'Attendance')

@section('content')
<section class="card" style="margin-bottom:1rem;">
    <form method="GET" action="{{ route('dashboard.attendances.index') }}" class="grid" style="align-items:end;">
        <input type="hidden" name="lang" value="{{ app()->getLocale() }}">
        <div class="field" style="margin:0;grid-column: span 4;">
            <label for="search">Search Students</label>
            <input id="search" type="text" name="search" value="{{ $search }}" placeholder="Name or email">
        </div>
        <div class="field" style="margin:0;grid-column: span 3;">
            <label for="date">Daily Date</label>
            <input id="date" type="date" name="date" value="{{ $attendanceDate }}">
        </div>
        <div class="field" style="margin:0;grid-column: span 3;">
            <label for="week">Week Of</label>
            <input id="week" type="date" name="week" value="{{ $weekDate }}">
        </div>
        <div class="actions" style="grid-column: span 2;justify-content:flex-end;">
            <button type="submit" class="btn">Load</button>
        </div>
    </form>
</section>

<section class="card" style="margin-bottom:1rem;">
    <h2 style="margin:0 0 .6rem;font-size:1.1rem;">Weekly Overview</h2>
    <p class="muted" style="margin:0 0 .6rem;">{{ $weekStart->format('M j') }} - {{ $weekEnd->format('M j, Y') }}</p>

    @php
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $weekDays[] = $day;
        }
        $statusLabel = function ($status) {
            return match($status) {
                'present' => 'P',
                'late' => 'S',
                'excused' => 'I',
                'absent' => 'A',
                default => '-'
            };
        };
    @endphp

    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                @foreach ($weekDays as $day)
                    <th style="text-align:center;">
                        {{ $day->format('D') }}
                        <div class="muted" style="font-size:.75rem;">{{ $day->format('m/d') }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($weeklyPage as $row)
                <tr>
                    <td>
                        <strong>{{ $row['student']->name }}</strong>
                        <div class="muted" style="font-size:.85rem;">{{ $row['student']->email }}</div>
                    </td>
                    @foreach ($weekDays as $day)
                        @php
                            $record = $row['byDate']->get($day->format('Y-m-d'));
                            $status = $record?->status;
                        @endphp
                        <td style="text-align:center;font-weight:700;">{{ $record ? $statusLabel($status) : '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">No attendance recorded for this week.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $weeklyPage->withQueryString()->links() }}</div>
</section>

<section class="card">
    <h2 style="margin:0 0 .6rem;font-size:1.1rem;">Daily Attendance</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Status</th>
                <th>Notes</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                @php
                    $record = $records->get($student->id);
                    $status = match($record?->status) {
                        'late' => 'sick',
                        default => $record?->status ?? 'present',
                    };
                    $formId = 'mark-'.$student->id;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $student->name }}</strong>
                        <div class="muted" style="font-size:.85rem;">{{ $student->email }}</div>
                        @perm('attendance.mark')
                            <form id="{{ $formId }}" method="POST" action="{{ route('dashboard.attendances.mark', ['lang' => app()->getLocale()]) }}">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">
                            </form>
                        @endperm
                    </td>
                    <td>
                        @perm('attendance.mark')
                            <select name="status" form="{{ $formId }}" required>
                                <option value="present" @selected($status === 'present')>Present</option>
                                <option value="sick" @selected($status === 'sick')>Sick</option>
                                <option value="excused" @selected($status === 'excused')>Excused</option>
                                <option value="absent" @selected($status === 'absent')>Absent</option>
                            </select>
                        @else
                            <span class="btn-outline" style="cursor:default;">{{ strtoupper($status) }}</span>
                        @endperm
                    </td>
                    <td>
                        @perm('attendance.mark')
                            <input name="notes" form="{{ $formId }}" type="text" value="{{ $record?->notes }}" placeholder="Optional" style="width:100%;">
                        @else
                            <span class="muted">{{ $record?->notes ?: '-' }}</span>
                        @endperm
                    </td>
                    <td>
                        @perm('attendance.mark')
                            <button type="submit" form="{{ $formId }}" class="btn">Save</button>
                        @endperm
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">No students found for this day. Add student users or adjust schedules.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

<div class="pagination">{{ $students->links() }}</div>
@endsection
