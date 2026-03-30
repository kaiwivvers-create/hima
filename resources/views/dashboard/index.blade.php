@extends('dashboard.layout')

@section('title', 'Dashboard Overview')
@section('page_title', 'Dashboard Overview')

@section('content')
@if ($isStudent ?? false)
    <div class="grid">
        <section class="card kpi">
            <p class="muted" style="margin:0 0 .25rem;">Your Attendance Rate</p>
            <p style="margin:0;font-size:1.5rem;font-weight:800;">{{ $attendanceRate }}%</p>
        </section>

        <section class="card" style="grid-column: span 12;">
            <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Your Recent Attendance</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $attendance)
                        <tr>
                            <td>{{ $attendance->attendance_date?->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($attendance->status) }}</td>
                            <td>{{ $attendance->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">No attendance records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="card" style="grid-column: span 12;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:.8rem;flex-wrap:wrap;">
                <div>
                    <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Your Weekly Attendance</h2>
                    <p class="muted" style="margin:0;">{{ $weekStart->format('M j') }} - {{ $weekEnd->format('M j, Y') }}</p>
                </div>
                <form method="GET" action="{{ route('dashboard') }}" class="actions" style="align-items:end;">
                    <input type="hidden" name="lang" value="{{ app()->getLocale() }}">
                    <div class="field" style="margin:0;">
                        <label for="week">Week Of</label>
                        <input id="week" type="date" name="week" value="{{ $weekDate }}">
                    </div>
                    <button type="submit" class="btn">Load</button>
                </form>
            </div>

            @php
                $weekDays = [];
                for ($i = 0; $i < 7; $i++) {
                    $weekDays[] = $weekStart->copy()->addDays($i);
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

            <table class="table" style="margin-top:.4rem;">
                <thead>
                    <tr>
                        @foreach ($weekDays as $day)
                            <th style="text-align:center;">
                                {{ $day->format('D') }}
                                <div class="muted" style="font-size:.75rem;">{{ $day->format('m/d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach ($weekDays as $day)
                            @php
                                $record = $weeklyRecords->get($day->format('Y-m-d'));
                            @endphp
                            <td style="text-align:center;font-weight:700;">{{ $record ? $statusLabel($record->status) : '' }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="card" style="grid-column: span 12;">
            <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Connection Requests</h2>
            <div class="grid">
                <section class="card" style="grid-column: span 6;">
                    <h3 style="margin:.1rem 0 .4rem;font-size:1rem;">Pending Requests</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Parent</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($connectionRequests as $request)
                                <tr>
                                    <td>{{ $request->name }} ({{ $request->email }})</td>
                                    <td>
                                        <div class="actions">
                                            <form method="POST" action="{{ route('dashboard.parent-connection.accept', ['requestId' => $request->id, 'lang' => app()->getLocale()]) }}">
                                                @csrf
                                                <button type="submit" class="btn">Accept</button>
                                            </form>
                                            <form method="POST" action="{{ route('dashboard.parent-connection.reject', ['requestId' => $request->id, 'lang' => app()->getLocale()]) }}">
                                                @csrf
                                                <button type="submit" class="btn-outline">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="muted">No requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>
                <section class="card" style="grid-column: span 6;">
                    <h3 style="margin:.1rem 0 .4rem;font-size:1rem;">Connected Parents</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                    @forelse ($connectedParents as $parent)
                        <tr>
                            <td>{{ $parent->name }}</td>
                            <td>{{ $parent->email }}</td>
                        </tr>
                            @empty
                                <tr><td colspan="2" class="muted">No parents connected.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>
            </div>
        </section>
    </div>
@elseif ($isParent ?? false)
    <div class="grid">
        <section class="card" style="grid-column: span 12;">
            <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Connection Requests</h2>
            <form method="POST" action="{{ route('dashboard.parent-connection.request', ['lang' => app()->getLocale()]) }}" class="actions" style="align-items:end; margin-bottom:.6rem;">
                @csrf
                <div class="field" style="margin:0; min-width:240px;">
                    <label for="student_email">Student Email</label>
                    <input id="student_email" name="student_email" type="email" required>
                </div>
                <button type="submit" class="btn">Send Request</button>
            </form>
        </section>

        <section class="card" style="grid-column: span 12;">
            <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Your Children</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($children as $child)
                        <tr>
                            <td>{{ $child->name }}</td>
                            <td>{{ $child->email }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="muted">No connected students yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@else
    <div class="grid">
        <section class="card kpi">
            <p class="muted" style="margin:0 0 .25rem;">Attendance Presence Rate</p>
            <p style="margin:0;font-size:1.5rem;font-weight:800;">{{ $attendanceRate }}%</p>
        </section>
        <section class="card kpi">
            <p class="muted" style="margin:0 0 .25rem;">Pending Payments</p>
            <p style="margin:0;font-size:1.5rem;font-weight:800;">{{ $pendingPayments }}</p>
        </section>
        <section class="card kpi">
            <p class="muted" style="margin:0 0 .25rem;">Pending Absence Verifications</p>
            <p style="margin:0;font-size:1.5rem;font-weight:800;">{{ $pendingAbsences }}</p>
        </section>
        <section class="card kpi">
            <p class="muted" style="margin:0 0 .25rem;">Total Students</p>
            <p style="margin:0;font-size:1.5rem;font-weight:800;">{{ $totalStudents }}</p>
        </section>

        <section class="card" style="grid-column: span 12;">
            <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Recent Attendance</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $attendance)
                        <tr>
                            <td>{{ $attendance->student?->name ?? '-' }}</td>
                            <td>{{ $attendance->attendance_date?->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($attendance->status) }}</td>
                            <td>{{ $attendance->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">No attendance records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination">{{ $recentAttendances->links() }}</div>
    </section>

        <section class="card" style="grid-column: span 6;">
            <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Recent Payments</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Invoice</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->student?->name ?? '-' }}</td>
                            <td>{{ $payment->invoice_no }}</td>
                            <td>{{ ucfirst($payment->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">No payments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="card" style="grid-column: span 6;">
            <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Recent Absence Notes</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Date</th>
                        <th>Verification</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAbsences as $absence)
                        <tr>
                            <td>{{ $absence->student?->name ?? '-' }}</td>
                            <td>{{ $absence->absence_date?->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($absence->verification_status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">No absences recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endif
@endsection
