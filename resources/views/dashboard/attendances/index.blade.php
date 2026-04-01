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
                'present' => __('Present Short'),
                'late' => __('Sick Short'),
                'excused' => __('Excused Short'),
                'absent' => __('Absent Short'),
                default => '-'
            };
        };
        $statusText = function (?string $status) {
            return match($status) {
                'present' => __('Present'),
                'late' => __('Sick'),
                'sick' => __('Sick'),
                'excused' => __('Excused'),
                'absent' => __('Absent'),
                default => '-',
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
                <th>Date</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Actions</th>
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
                            </form>
                        @endperm
                    </td>
                    <td>
                        @perm('attendance.mark')
                            <input name="attendance_date" form="{{ $formId }}" type="date" value="{{ $attendanceDate }}" required>
                        @else
                            <span class="muted">{{ $attendanceDate }}</span>
                        @endperm
                    </td>
                    <td>
                        @perm('attendance.mark')
                            <select name="status" form="{{ $formId }}" required>
                                <option value="present" @selected($status === 'present')>{{ __('Present') }}</option>
                                <option value="sick" @selected($status === 'sick')>{{ __('Sick') }}</option>
                                <option value="excused" @selected($status === 'excused')>{{ __('Excused') }}</option>
                                <option value="absent" @selected($status === 'absent')>{{ __('Absent') }}</option>
                            </select>
                        @else
                            <span class="btn-outline" style="cursor:default;">{{ $statusText($status) }}</span>
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
                        <div class="actions">
                            @if (($showStudentAttendanceSummary ?? false) && $studentAttendanceSummary->has($student->id))
                                <button type="button" class="btn-outline" data-modal-open="attendance-summary-{{ $student->id }}">{{ __('View') }}</button>
                            @endif
                            @perm('attendance.mark')
                                <button type="submit" form="{{ $formId }}" class="btn">Save</button>
                            @endperm
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No students found for this day. Add student users or adjust schedules.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

<div class="pagination">{{ $students->links() }}</div>

@if ($showStudentAttendanceSummary ?? false)
    @foreach ($students as $student)
        @php
            $summary = $studentAttendanceSummary->get($student->id);
        @endphp
        @if ($summary)
            <div class="modal" id="attendance-summary-{{ $student->id }}">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-card">
                    <div class="modal-head">
                        <h2>{{ __('Student Attendance Summary') }}</h2>
                        <button class="btn-outline" type="button" data-modal-close>{{ __('Close') }}</button>
                    </div>
                    <p style="margin:0 0 .7rem;font-weight:700;">{{ $summary['name'] }} <span class="muted" style="font-weight:600;">({{ $summary['email'] }})</span></p>
                    <table class="table">
                        <tbody>
                            <tr>
                                <th style="width:45%;">{{ __('Attendance %') }}</th>
                                <td>{{ number_format((float) $summary['attendance_rate'], 1) }}%</td>
                            </tr>
                            <tr>
                                <th>{{ __('Attendance Amount') }}</th>
                                <td>{{ $summary['total_attendance'] }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Present') }}</th>
                                <td>{{ $summary['present_count'] }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Late') }}</th>
                                <td>{{ $summary['late_count'] }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Excused') }}</th>
                                <td>{{ $summary['excused_count'] }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Absent') }}</th>
                                <td>{{ $summary['absent_count'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endforeach
@endif
@endsection
