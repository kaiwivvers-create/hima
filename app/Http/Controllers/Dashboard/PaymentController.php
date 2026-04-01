<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payments.view')->only(['index']);
        $this->middleware('permission:payments.create')->only(['create', 'store']);
        $this->middleware('permission:payments.update')->only(['edit', 'update']);
        $this->middleware('permission:payments.delete')->only(['destroy']);
    }

    public function index(): View
    {
        $user = request()->user();
        $query = Payment::with('student')->orderBy('due_date', 'asc');
        $students = User::where('role', 'student')->orderBy('name')->get();
        $studentCards = collect();
        $modalPayments = collect();

        if ($user && $user->role === 'student') {
            $query->where('student_id', $user->id);
            $students = $students->where('id', $user->id);
        } elseif ($user && $user->role === 'parent') {
            $studentIds = DB::table('parent_student')
                ->where('parent_user_id', $user->id)
                ->pluck('student_user_id')
                ->all();
            $query->whereIn('student_id', $studentIds);
            $students = $students->whereIn('id', $studentIds);
        }

        $isAdminView = $user && $user->role !== 'parent' && $user->role !== 'student';
        $latestProofByPaymentId = collect();
        if ($isAdminView) {
            $payments = $query->get();
            $modalPayments = $payments;
            $paymentsByStudent = $payments->groupBy('student_id');
            $studentCards = $students->map(function ($student) use ($paymentsByStudent) {
                $studentPayments = $paymentsByStudent->get($student->id, collect());
                $total = (float) $studentPayments->sum('amount');
                $paid = (float) $studentPayments->sum('paid_amount');
                $percentRaw = $total > 0 ? round(($paid / $total) * 100, 1) : 0.0;
                $fillPercent = max(0, min(100, $percentRaw));
                $status = 'none';
                if ($total > 0) {
                    $status = $percentRaw >= 100 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');
                }

                return [
                    'student' => $student,
                    'payments' => $studentPayments,
                    'total' => $total,
                    'paid' => $paid,
                    'percent' => $percentRaw,
                    'fill_percent' => $fillPercent,
                    'status' => $status,
                ];
            });
        } else {
            $payments = $query->paginate(10);
            $modalPayments = $payments->getCollection();
            if ($user && $user->role === 'parent') {
                $paymentIds = $modalPayments->pluck('id')->all();
                if (!empty($paymentIds)) {
                    $latestProofByPaymentId = PaymentProof::query()
                        ->whereIn('payment_id', $paymentIds)
                        ->orderByDesc('created_at')
                        ->get()
                        ->unique('payment_id')
                        ->keyBy('payment_id');
                }
            }
        }

        return view('dashboard.payments.index', [
            'payments' => $payments,
            'students' => $students,
            'studentCards' => $studentCards,
            'modalPayments' => $modalPayments,
            'isAdminView' => $isAdminView,
            'latestProofByPaymentId' => $latestProofByPaymentId,
        ]);
    }

    public function create(): View
    {
        $user = request()->user();
        $students = User::where('role', 'student')->orderBy('name')->get();
        if ($user && $user->role === 'student') {
            $students = $students->where('id', $user->id);
        } elseif ($user && $user->role === 'parent') {
            $studentIds = DB::table('parent_student')
                ->where('parent_user_id', $user->id)
                ->pluck('student_user_id')
                ->all();
            $students = $students->whereIn('id', $studentIds);
        }

        return view('dashboard.payments.create', [
            'students' => $students,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'invoice_no' => ['required', 'string', 'max:255', 'unique:payments,invoice_no'],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:amount'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'partial', 'paid'])],
        ]);

        $user = $request->user();
        if ($user && $user->role === 'student') {
            $validated['student_id'] = $user->id;
        } elseif ($user && $user->role === 'parent') {
            $allowedIds = DB::table('parent_student')
                ->where('parent_user_id', $user->id)
                ->pluck('student_user_id')
                ->all();
            if (!in_array((int) $validated['student_id'], $allowedIds, true)) {
                abort(403);
            }
        }

        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

        $payment = Payment::create($validated);

        ActivityLogger::log(
            'payment.created',
            'payment',
            $payment->id,
            'Payment created.',
            null,
            ActivityLogger::snapshot($payment, 'payment')
        );

        return redirect()->route('dashboard.payments.index', ['lang' => app()->getLocale()])
            ->with('success', 'Payment record created successfully.');
    }

    public function edit(Payment $payment): View
    {
        $user = request()->user();
        if ($user && $user->role === 'student' && (int) $payment->student_id !== (int) $user->id) {
            abort(403);
        }
        if ($user && $user->role === 'parent') {
            $allowedIds = DB::table('parent_student')
                ->where('parent_user_id', $user->id)
                ->pluck('student_user_id')
                ->all();
            if (!in_array((int) $payment->student_id, $allowedIds, true)) {
                abort(403);
            }
        }

        return view('dashboard.payments.edit', [
            'payment' => $payment,
            'students' => User::where('role', 'student')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'invoice_no' => ['required', 'string', 'max:255', 'unique:payments,invoice_no,' . $payment->id],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:amount'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'partial', 'paid'])],
        ]);

        $user = $request->user();
        if ($user && $user->role === 'student') {
            if ((int) $payment->student_id !== (int) $user->id) {
                abort(403);
            }
            $validated['student_id'] = $user->id;
        } elseif ($user && $user->role === 'parent') {
            $allowedIds = DB::table('parent_student')
                ->where('parent_user_id', $user->id)
                ->pluck('student_user_id')
                ->all();
            if (!in_array((int) $payment->student_id, $allowedIds, true)) {
                abort(403);
            }
        }

        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;

        $before = ActivityLogger::snapshot($payment, 'payment');
        $payment->update($validated);

        ActivityLogger::log(
            'payment.updated',
            'payment',
            $payment->id,
            'Payment updated.',
            $before,
            ActivityLogger::snapshot($payment, 'payment')
        );

        return redirect()->route('dashboard.payments.index', ['lang' => app()->getLocale()])
            ->with('success', 'Payment record updated successfully.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $before = ActivityLogger::snapshot($payment, 'payment');
        $payment->delete();

        ActivityLogger::log(
            'payment.deleted',
            'payment',
            $payment->id,
            'Payment deleted.',
            $before,
            null
        );

        return redirect()->route('dashboard.payments.index', ['lang' => app()->getLocale()])
            ->with('success', 'Payment record deleted successfully.');
    }

    public function pay(Request $request, Payment $payment): RedirectResponse
    {
        return redirect()->route('dashboard.payments.index', [
            'lang' => app()->getLocale(),
        ])->withErrors(['payment' => 'Direct payment is disabled. Please submit payment proof for admin review.']);
    }

    public function receipt(Payment $payment): View
    {
        $user = request()->user();
        if (!$user) {
            abort(401);
        }

        if ($user->role === 'parent') {
            $allowedIds = DB::table('parent_student')
                ->where('parent_user_id', $user->id)
                ->pluck('student_user_id')
                ->all();
            if (!in_array((int) $payment->student_id, $allowedIds, true)) {
                abort(403);
            }
        } elseif ($user->role === 'student' && (int) $payment->student_id !== (int) $user->id) {
            abort(403);
        }

        return view('dashboard.payments.receipt', [
            'payment' => $payment->load('student'),
        ]);
    }

    public function generatePlan(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || in_array($user->role, ['parent', 'student'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'plan' => ['required', Rule::in(['monthly', 'bi_monthly', 'triannual', 'yearly'])],
        ]);

        $student = User::findOrFail($validated['student_id']);
        $tuition = (float) ($student->tuition_amount ?? 0);
        if ($tuition <= 0) {
            return back()->withErrors(['plan' => 'Tuition amount not set for this student.']);
        }

        $plan = $validated['plan'];
        $planConfig = match ($plan) {
            'monthly' => ['count' => 12, 'interval' => 1],
            'bi_monthly' => ['count' => 6, 'interval' => 2],
            'triannual' => ['count' => 3, 'interval' => 4],
            'yearly' => ['count' => 1, 'interval' => 12],
        };

        $count = $planConfig['count'];
        $interval = $planConfig['interval'];
        $year = now()->year;
        $base = round($tuition / $count, 2);

        $start = Carbon::now()->startOfMonth();
        $created = 0;

        for ($i = 1; $i <= $count; $i++) {
            $dueDate = $start->copy()->addMonths(($i - 1) * $interval)->toDateString();
            $amount = $i === $count ? round($tuition - ($base * ($count - 1)), 2) : $base;
            $invoice = 'TUITION-'.$student->id.'-'.$year.'-'.$i;

            $exists = Payment::where('invoice_no', $invoice)->exists();
            if ($exists) {
                continue;
            }

            Payment::create([
                'student_id' => $student->id,
                'invoice_no' => $invoice,
                'amount' => $amount,
                'paid_amount' => 0,
                'due_date' => $dueDate,
                'paid_at' => null,
                'status' => 'pending',
            ]);
            $created++;
        }

        return back()->with('success', $created.' payment(s) generated.');
    }
}
