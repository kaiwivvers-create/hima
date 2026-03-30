@extends('dashboard.layout')

@section('title', 'Receipt')
@section('page_title', 'Payment Receipt')

@section('content')
<section class="card" style="max-width:720px;">
    <h2 style="margin:.1rem 0 .4rem;font-size:1.1rem;">Receipt</h2>
    <div class="grid">
        <div class="card" style="grid-column: span 6;">
            <p class="muted" style="margin:0 0 .35rem;">Invoice</p>
            <p style="margin:0;font-weight:700;">{{ $payment->invoice_no }}</p>
        </div>
        <div class="card" style="grid-column: span 6;">
            <p class="muted" style="margin:0 0 .35rem;">Date</p>
            <p style="margin:0;font-weight:700;">{{ $payment->paid_at?->format('Y-m-d') ?? '-' }}</p>
        </div>
        <div class="card" style="grid-column: span 6;">
            <p class="muted" style="margin:0 0 .35rem;">Student</p>
            <p style="margin:0;font-weight:700;">{{ $payment->student?->name ?? '-' }}</p>
            <p class="muted" style="margin:.2rem 0 0;">{{ $payment->student?->email ?? '' }}</p>
        </div>
        <div class="card" style="grid-column: span 6;">
            <p class="muted" style="margin:0 0 .35rem;">Status</p>
            <p style="margin:0;font-weight:700;">{{ ucfirst($payment->status) }}</p>
        </div>
        <div class="card" style="grid-column: span 6;">
            <p class="muted" style="margin:0 0 .35rem;">Amount</p>
            <p style="margin:0;font-weight:700;">{{ number_format((float) $payment->amount, 2) }}</p>
        </div>
        <div class="card" style="grid-column: span 6;">
            <p class="muted" style="margin:0 0 .35rem;">Paid</p>
            <p style="margin:0;font-weight:700;">{{ number_format((float) $payment->paid_amount, 2) }}</p>
        </div>
    </div>

    <div class="actions" style="margin-top:.8rem;">
        <a class="btn" href="{{ route('dashboard.payments.index', ['lang' => app()->getLocale()]) }}">Back</a>
        <button class="btn-outline" type="button" onclick="window.print()">Download</button>
    </div>
</section>
@endsection
