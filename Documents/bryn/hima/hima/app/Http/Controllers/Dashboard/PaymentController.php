<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('dashboard.payments.index', [
            'payments' => Payment::with('student')->latest('due_date')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.payments.create', [
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'invoice_no' => ['required', 'string', 'max:255', 'unique:payments,invoice_no'],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:amount'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'partial', 'paid'])],
        ]);

        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

        Payment::create($validated);

        return redirect()->route('dashboard.payments.index', ['lang' => app()->getLocale()])
            ->with('success', 'Payment record created successfully.');
    }

    public function edit(Payment $payment): View
    {
        return view('dashboard.payments.edit', [
            'payment' => $payment,
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'invoice_no' => ['required', 'string', 'max:255', 'unique:payments,invoice_no,' . $payment->id],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:amount'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'partial', 'paid'])],
        ]);

        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

        $payment->update($validated);

        return redirect()->route('dashboard.payments.index', ['lang' => app()->getLocale()])
            ->with('success', 'Payment record updated successfully.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()->route('dashboard.payments.index', ['lang' => app()->getLocale()])
            ->with('success', 'Payment record deleted successfully.');
    }
}
