@extends('dashboard.layout')

@section('title', 'Edit Payment')
@section('page_title', 'Edit Payment')

@section('content')
<section class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('dashboard.payments.update', ['payment' => $payment, 'lang' => app()->getLocale()]) }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" required>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) old('student_id', $payment->student_id) === (string) $student->id)>{{ $student->name }} ({{ $student->class_name }})</option>
                @endforeach
            </select>
            @error('student_id')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="invoice_no">Invoice Number</label>
            <input id="invoice_no" name="invoice_no" type="text" value="{{ old('invoice_no', $payment->invoice_no) }}" required>
            @error('invoice_no')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="amount">Total Amount</label>
            <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', $payment->amount) }}" required>
            @error('amount')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="paid_amount">Paid Amount</label>
            <input id="paid_amount" name="paid_amount" type="number" min="0" step="0.01" value="{{ old('paid_amount', $payment->paid_amount) }}">
            @error('paid_amount')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="due_date">Due Date</label>
            <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $payment->due_date?->format('Y-m-d')) }}" required>
            @error('due_date')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="paid_at">Paid At (optional)</label>
            <input id="paid_at" name="paid_at" type="date" value="{{ old('paid_at', $payment->paid_at?->format('Y-m-d')) }}">
            @error('paid_at')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="pending" @selected(old('status', $payment->status) === 'pending')>Pending</option>
                <option value="partial" @selected(old('status', $payment->status) === 'partial')>Partial</option>
                <option value="paid" @selected(old('status', $payment->status) === 'paid')>Paid</option>
            </select>
            @error('status')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn">Update</button>
            <a class="btn-outline" href="{{ route('dashboard.payments.index', ['lang' => app()->getLocale()]) }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
