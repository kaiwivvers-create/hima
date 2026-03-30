@extends('dashboard.layout')

@section('title', 'Dashboard Overview')
@section('page_title', 'Dashboard Overview')

@section('content')
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
@endsection
