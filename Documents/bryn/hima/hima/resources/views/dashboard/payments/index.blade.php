@extends('dashboard.layout')

@section('title', 'Payments')
@section('page_title', 'Payments')

@section('content')
<div class="page-actions">
    <a class="btn" href="{{ route('dashboard.payments.create', ['lang' => app()->getLocale()]) }}">Add Payment</a>
</div>

<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Invoice</th>
                <th>Amount</th>
                <th>Paid Amount</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment->student?->name ?? '-' }}</td>
                    <td>{{ $payment->invoice_no }}</td>
                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                    <td>{{ number_format((float) $payment->paid_amount, 2) }}</td>
                    <td>{{ $payment->due_date?->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($payment->status) }}</td>
                    <td>
                        <div class="actions">
                            <a class="btn-outline" href="{{ route('dashboard.payments.edit', ['payment' => $payment, 'lang' => app()->getLocale()]) }}">Edit</a>
                            <form method="POST" action="{{ route('dashboard.payments.destroy', ['payment' => $payment, 'lang' => app()->getLocale()]) }}" onsubmit="return confirm('Delete this payment record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">No payment records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $payments->withQueryString()->links() }}</div>
</section>
@endsection
