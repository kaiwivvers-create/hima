<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentProofController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if(in_array($user->role, ['parent', 'student'], true), 403);

        $proofs = PaymentProof::query()
            ->with(['payment.student', 'parent', 'reviewer'])
            ->latest()
            ->paginate(20);

        return view('dashboard.payments.proofs', [
            'proofs' => $proofs,
        ]);
    }

    public function store(Request $request, Payment $payment): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if($user->role !== 'parent', 403);

        $allowedStudentIds = DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->pluck('student_user_id')
            ->all();
        if (!in_array((int) $payment->student_id, $allowedStudentIds, true)) {
            abort(403);
        }

        if ($payment->status === 'paid') {
            return back()->withErrors(['proof' => 'This invoice is already paid.']);
        }

        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'max:5120'],
            'proof_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $path = $request->file('proof_image')->store('payment-proofs', 'public');

        PaymentProof::create([
            'payment_id' => $payment->id,
            'parent_user_id' => $user->id,
            'image_path' => $path,
            'note' => $validated['proof_note'] ?? null,
            'status' => 'pending',
        ]);

        $admins = User::query()
            ->whereIn('role', ['admin', 'super admin'])
            ->get(['id']);
        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'payment.proof.submitted',
                'title' => 'Payment proof submitted',
                'body' => $user->name.' submitted proof for invoice '.$payment->invoice_no.'.',
                'data' => [
                    'payment_id' => $payment->id,
                    'invoice_no' => $payment->invoice_no,
                    'parent_user_id' => $user->id,
                ],
            ]);
        }

        return back()->with('success', 'Payment proof submitted for review.');
    }

    public function approve(Request $request, PaymentProof $proof): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if(in_array($user->role, ['parent', 'student'], true), 403);

        if ($proof->status === 'approved') {
            return back();
        }

        $proof->status = 'approved';
        $proof->reviewed_by = $user->id;
        $proof->reviewed_at = now();
        $proof->review_note = $request->input('review_note');
        $proof->save();

        $payment = $proof->payment;
        if ($payment && $payment->status !== 'paid') {
            $payment->paid_amount = $payment->amount;
            $payment->paid_at = now()->toDateString();
            $payment->status = 'paid';
            $payment->save();
        }

        return back()->with('success', 'Payment proof approved. Invoice marked paid.');
    }

    public function reject(Request $request, PaymentProof $proof): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if(in_array($user->role, ['parent', 'student'], true), 403);

        if ($proof->status === 'rejected') {
            return back();
        }

        $proof->status = 'rejected';
        $proof->reviewed_by = $user->id;
        $proof->reviewed_at = now();
        $proof->review_note = $request->input('review_note');
        $proof->save();

        return back()->with('success', 'Payment proof rejected.');
    }
}

