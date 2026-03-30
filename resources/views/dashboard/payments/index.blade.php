@extends('dashboard.layout')

@section('title', 'Payments')
@section('page_title', 'Payments')

@section('content')
@if (request()->query('receipt'))
    <section class="card" style="margin-bottom:.8rem;">
        <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Receipt Ready</h2>
        <p class="muted" style="margin:.2rem 0 .6rem;">Your payment was recorded. You can view or download the receipt.</p>
        <div class="actions">
            <a class="btn" href="{{ route('dashboard.payments.receipt', ['payment' => request()->query('receipt'), 'lang' => app()->getLocale()]) }}">View Receipt</a>
        </div>
    </section>
@endif
<div class="page-actions">
    @perm('payments.create')
        @if (auth()->user()?->role !== 'parent')
            <button class="btn" type="button" data-modal-open="payment-create-modal">Add Payment</button>
        @endif
    @endperm
</div>

@if (auth()->user()?->role === 'parent')
    <section class="card" style="margin-bottom:.8rem;">
        <h2 style="margin:.1rem 0 .4rem;font-size:1.05rem;">Generate Tuition Plan</h2>
        @if ($errors->has('plan'))
            <div class="alert alert-error" style="margin-bottom:.6rem;">{{ $errors->first('plan') }}</div>
        @endif
        <form method="POST" action="{{ route('dashboard.payments.plan', ['lang' => app()->getLocale()]) }}" class="actions" style="align-items:end;">
            @csrf
            <div class="field" style="margin:0; min-width:220px;">
                <label for="plan-student">Student</label>
                <select id="plan-student" name="student_id" required>
                    <option value="">Select student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                    @endforeach
                </select>
                @error('student_id')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field" style="margin:0; min-width:200px;">
                <label for="plan-type">Plan</label>
                <select id="plan-type" name="plan" required>
                    <option value="monthly">Monthly (12x)</option>
                    <option value="bi_monthly">Every 2 months (6x)</option>
                    <option value="triannual">3x per year</option>
                    <option value="yearly">Yearly (1x)</option>
                </select>
                @error('plan')<div class="error">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn">Generate</button>
        </form>
    </section>
@endif

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
                            @if ($payment->status === 'paid')
                                <button class="btn-outline" type="button" data-modal-open="payment-receipt-{{ $payment->id }}">View</button>
                            @endif
                            @if (auth()->user()?->role === 'parent' && $payment->status !== 'paid')
                                <button class="btn" type="button" data-modal-open="payment-pay-{{ $payment->id }}">Pay</button>
                            @endif
                            @if (auth()->user()?->role !== 'parent' && auth()->user()?->role !== 'student')
                                @perm('payments.update')
                                    <button class="btn-outline" type="button" data-modal-open="payment-edit-{{ $payment->id }}">Edit</button>
                                @endperm
                                @perm('payments.delete')
                                    <button class="btn btn-danger" type="button" data-modal-open="payment-delete-{{ $payment->id }}">Delete</button>
                                @endperm
                            @endif
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

@perm('payments.create')
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
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
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
@endperm

    @foreach ($payments as $payment)
        @if (auth()->user()?->role === 'parent' && $payment->status !== 'paid')
            <div class="modal" id="payment-pay-{{ $payment->id }}">
                <div class="modal-backdrop" data-modal-close></div>
                <div class="modal-card">
                    <div class="modal-head">
                        <h2>Scan to Pay</h2>
                        <button class="btn-outline" type="button" data-modal-close>Close</button>
                    </div>
                    <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
                        <div style="width:180px;height:180px;border-radius:12px;border:1px solid var(--line);background:repeating-linear-gradient(45deg,#fff7d1,#fff7d1 8px,#ffe9a8 8px,#ffe9a8 16px);display:flex;align-items:center;justify-content:center;font-weight:800;">
                            QR
                        </div>
                        <div style="flex:1; min-width:220px;">
                            <p style="margin:.2rem 0;"><strong>Invoice:</strong> {{ $payment->invoice_no }}</p>
                            <p style="margin:.2rem 0;"><strong>Amount:</strong> {{ number_format((float) $payment->amount, 2) }}</p>
                            <p class="muted" style="margin:.2rem 0;">This is a sandbox QR. Click “Paid” to simulate payment.</p>
                            <form method="POST" action="{{ route('dashboard.payments.pay', ['payment' => $payment, 'lang' => app()->getLocale()]) }}">
                                @csrf
                                <button type="submit" class="btn">Paid</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (auth()->user()?->role !== 'parent' && auth()->user()?->role !== 'student')
        @perm('payments.update')
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
                                <option value="{{ $student->id }}" @selected((int) $payment->student_id === (int) $student->id)>{{ $student->name }} ({{ $student->email }})</option>
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
        @endperm
        @endif
        @if ($payment->status === 'paid')
        <div class="modal" id="payment-receipt-{{ $payment->id }}">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-card" style="max-width:520px;">
                <div class="modal-head">
                    <h2>Receipt</h2>
                    <button class="btn-outline" type="button" data-modal-close aria-label="Close receipt modal">&times;</button>
                </div>
                <div class="card" style="margin:0; border:none;">
                    <div class="grid" style="grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem;">
                        <div>
                            <p class="muted" style="margin:0 0 .35rem;">Invoice</p>
                            <p style="margin:0;font-weight:700;">{{ $payment->invoice_no }}</p>
                        </div>
                        <div>
                            <p class="muted" style="margin:0 0 .35rem;">Date</p>
                            <p style="margin:0;font-weight:700;">{{ $payment->paid_at?->format('Y-m-d') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="muted" style="margin:0 0 .35rem;">Student</p>
                            <p style="margin:0;font-weight:700;">{{ $payment->student?->name ?? '-' }}</p>
                            <p class="muted" style="margin:.2rem 0 0;">{{ $payment->student?->email ?? '' }}</p>
                        </div>
                        <div>
                            <p class="muted" style="margin:0 0 .35rem;">Status</p>
                            <p style="margin:0;font-weight:700;">{{ ucfirst($payment->status) }}</p>
                        </div>
                        <div>
                            <p class="muted" style="margin:0 0 .35rem;">Amount</p>
                            <p style="margin:0;font-weight:700;">{{ number_format((float) $payment->amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="muted" style="margin:0 0 .35rem;">Paid</p>
                            <p style="margin:0;font-weight:700;">{{ number_format((float) $payment->paid_amount, 2) }}</p>
                        </div>
                    </div>
                </div>
                <div class="actions" style="margin-top:.8rem;">
                    <button class="btn" type="button" onclick="window.print()">Download</button>
                    <button class="btn-outline" type="button" data-modal-close>Close</button>
                </div>
            </div>
        </div>
        @endif

        @if (auth()->user()?->role !== 'parent' && auth()->user()?->role !== 'student')
        @perm('payments.delete')
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
    @endperm
    @endif
    @endforeach
@endsection
