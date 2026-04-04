@extends('dashboard.layout')

@php use App\Models\Payment; @endphp

@section('title', 'Create Payment')
@section('page_title', 'Create Payment')

@section('content')
<section class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('dashboard.payments.store', ['lang' => app()->getLocale()]) }}">
        @csrf

        <div class="field">
            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" required>
                <option value="">Select student</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>{{ $student->name }} ({{ $student->email }})</option>
                @endforeach
            </select>
            @error('student_id')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="invoice_no">Invoice Number</label>
            <input id="invoice_no" name="invoice_no" type="text" value="{{ old('invoice_no') }}" required>
            @error('invoice_no')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="amount">Total Amount</label>
            <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount') }}" required>
            @error('amount')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="paid_amount">Paid Amount</label>
            <input id="paid_amount" name="paid_amount" type="number" min="0" step="0.01" value="{{ old('paid_amount', 0) }}">
            @error('paid_amount')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="due_date">Due Date</label>
            <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}" required>
            @error('due_date')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="paid_at">Paid At (optional)</label>
            <input id="paid_at" name="paid_at" type="date" value="{{ old('paid_at') }}">
            @error('paid_at')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                <option value="partial" @selected(old('status') === 'partial')>Partial</option>
                <option value="paid" @selected(old('status') === 'paid')>Paid</option>
            </select>
            @error('status')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" required>
                @foreach (Payment::METHODS as $method)
                    <option value="{{ $method }}" @selected(old('payment_method', 'transfer') === $method)>{{ ucfirst($method) }}</option>
                @endforeach
            </select>
            @error('payment_method')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn">Save</button>
            <a class="btn-outline" href="{{ route('dashboard.payments.index', ['lang' => app()->getLocale()]) }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
