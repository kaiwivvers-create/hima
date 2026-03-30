@extends('dashboard.layout')

@section('title', 'Payments')
@section('page_title', 'Payments')

@section('content')
<div class="page-actions">
    <button class="btn" type="button" data-modal-open="payment-create-modal">Add Payment</button>
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
                            <button class="btn-outline" type="button" data-modal-open="payment-edit-{{ $payment->id }}">Edit</button>
                            <button class="btn btn-danger" type="button" data-modal-open="payment-delete-{{ $payment->id }}">Delete</button>
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

<div class="modal" id="payment-create-modal">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card">
        <div class="modal-head">
            <h2>Add Payment</h2>
            <button class="btn-outline" type="button" data-modal-close>Close</button>
        </div>
        <form method="POST" action="{{ route('dashboard.payments.store', ['lang' => app()->getLocale()]) }}">
            @csrf
            <div class="field">
                <label for="create-payment-student">Student</label>
                <select id="create-payment-student" name="student_id" required>
                    <option value="">Select student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->class_name }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="create-invoice">Invoice Number</label>
                <input id="create-invoice" name="invoice_no" type="text" required>
            </div>
            <div class="field">
                <label for="create-amount">Total Amount</label>
                <input id="create-amount" name="amount" type="number" min="0" step="0.01" required>
            </div>
            <div class="field">
                <label for="create-paid-amount">Paid Amount</label>
                <input id="create-paid-amount" name="paid_amount" type="number" min="0" step="0.01" value="0">
            </div>
            <div class="field">
                <label for="create-due-date">Due Date</label>
                <input id="create-due-date" name="due_date" type="date" required>
            </div>
            <div class="field">
                <label for="create-paid-at">Paid At</label>
                <input id="create-paid-at" name="paid_at" type="date">
            </div>
            <div class="field">
                <label for="create-payment-status">Status</label>
                <select id="create-payment-status" name="status" required>
                    <option value="pending">Pending</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Save</button>
            </div>
        </form>
    </div>
</div>

@foreach ($payments as $payment)
    <div class="modal" id="payment-edit-{{ $payment->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Edit Payment</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <form method="POST" action="{{ route('dashboard.payments.update', ['payment' => $payment, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="edit-payment-student-{{ $payment->id }}">Student</label>
                    <select id="edit-payment-student-{{ $payment->id }}" name="student_id" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected((int) $payment->student_id === (int) $student->id)>{{ $student->name }} ({{ $student->class_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="edit-invoice-{{ $payment->id }}">Invoice Number</label>
                    <input id="edit-invoice-{{ $payment->id }}" name="invoice_no" type="text" value="{{ $payment->invoice_no }}" required>
                </div>
                <div class="field">
                    <label for="edit-amount-{{ $payment->id }}">Total Amount</label>
                    <input id="edit-amount-{{ $payment->id }}" name="amount" type="number" min="0" step="0.01" value="{{ $payment->amount }}" required>
                </div>
                <div class="field">
                    <label for="edit-paid-amount-{{ $payment->id }}">Paid Amount</label>
                    <input id="edit-paid-amount-{{ $payment->id }}" name="paid_amount" type="number" min="0" step="0.01" value="{{ $payment->paid_amount }}">
                </div>
                <div class="field">
                    <label for="edit-due-date-{{ $payment->id }}">Due Date</label>
                    <input id="edit-due-date-{{ $payment->id }}" name="due_date" type="date" value="{{ $payment->due_date?->format('Y-m-d') }}" required>
                </div>
                <div class="field">
                    <label for="edit-paid-at-{{ $payment->id }}">Paid At</label>
                    <input id="edit-paid-at-{{ $payment->id }}" name="paid_at" type="date" value="{{ $payment->paid_at?->format('Y-m-d') }}">
                </div>
                <div class="field">
                    <label for="edit-status-{{ $payment->id }}">Status</label>
                    <select id="edit-status-{{ $payment->id }}" name="status" required>
                        <option value="pending" @selected($payment->status === 'pending')>Pending</option>
                        <option value="partial" @selected($payment->status === 'partial')>Partial</option>
                        <option value="paid" @selected($payment->status === 'paid')>Paid</option>
                    </select>
                </div>
                <div class="actions">
                    <button type="submit" class="btn">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="payment-delete-{{ $payment->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Delete Payment</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <p>Delete invoice <strong>{{ $payment->invoice_no }}</strong>?</p>
            <form method="POST" action="{{ route('dashboard.payments.destroy', ['payment' => $payment, 'lang' => app()->getLocale()]) }}">
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
