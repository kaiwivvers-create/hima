<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
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
        }

        return view('dashboard.payments.index', [
            'payments' => $payments,
            'students' => $students,
            'studentCards' => $studentCards,
            'modalPayments' => $modalPayments,
            'isAdminView' => $isAdminView,
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
        $payment->load('student');

        ActivityLogger::log(
            'payment.created',
            'payment',
            $payment->id,
            'Payment created.',
            null,
            ActivityLogger::snapshot($payment, 'payment')
        );

        $studentName = $payment->student?->name ?? 'the student';
        NotificationService::notifyStudentAndParents(
            $payment->student_id,
            'New payment added',
            'Invoice '.$payment->invoice_no.' for '.$studentName.' has been added with status '.ucfirst($payment->status).'.',
            'payment.created',
            ['payment_id' => $payment->id, 'student_id' => $payment->student_id]
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
        $previousStatus = $payment->status;
        $previousPaidAmount = (float) $payment->paid_amount;

        $before = ActivityLogger::snapshot($payment, 'payment');
        $payment->update($validated);
        $payment->load('student');

        ActivityLogger::log(
            'payment.updated',
            'payment',
            $payment->id,
            'Payment updated.',
            $before,
            ActivityLogger::snapshot($payment, 'payment')
        );

        $statusChanged = $previousStatus !== $payment->status;
        $amountChanged = $previousPaidAmount !== (float) $payment->paid_amount;
        if ($statusChanged || $amountChanged) {
            $studentName = $payment->student?->name ?? 'the student';
            NotificationService::notifyStudentAndParents(
                $payment->student_id,
                'Payment updated',
                'Invoice '.$payment->invoice_no.' for '.$studentName.' is now marked as '.ucfirst($payment->status).'.',
                'payment.updated',
                ['payment_id' => $payment->id, 'student_id' => $payment->student_id]
            );
        }

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
        $user = $request->user();
        if (!$user || $user->role !== 'parent') {
            abort(403);
        }

        $allowedIds = DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->pluck('student_user_id')
            ->all();
        if (!in_array((int) $payment->student_id, $allowedIds, true)) {
            abort(403);
        }

        $payment->paid_amount = $payment->amount;
        $payment->paid_at = now();
        $payment->status = 'paid';
        $payment->save();
        $payment->load('student');

        ActivityLogger::log(
            'payment.paid',
            'payment',
            $payment->id,
            'Payment marked as paid.',
            null,
            ActivityLogger::snapshot($payment, 'payment')
        );

        $studentName = $payment->student?->name ?? 'the student';
        NotificationService::notifyAdmins(
            'Payment received',
            $user->name.' paid invoice '.$payment->invoice_no.' for '.$studentName.'.',
            'payment.paid',
            ['payment_id' => $payment->id, 'student_id' => $payment->student_id, 'paid_by_user_id' => $user->id]
        );
        NotificationService::notifyStudentAndParents(
            $payment->student_id,
            'Payment completed',
            'Invoice '.$payment->invoice_no.' for '.$studentName.' has been marked as paid.',
            'payment.paid',
            ['payment_id' => $payment->id, 'student_id' => $payment->student_id],
            true,
            true,
            [$user->id]
        );

        return redirect()->route('dashboard.payments.index', [
            'lang' => app()->getLocale(),
            'receipt' => $payment->id,
        ])->with('success', 'Payment completed.');
    }

    public function receipt(Request $request, Payment $payment)
    {
        $user = $request->user();
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

        $payment->load('student');

        if ($request->query('action') === 'pdf') {
            $pdf = Pdf::loadView('dashboard.payments.receipt-pdf', [
                'payment' => $payment,
                'appName' => $this->resolveAppName(),
                'appLogoUrl' => $this->resolveAppLogoUrl(),
            ])->setPaper('a4');

            return $pdf->download('receipt-'.$payment->invoice_no.'.pdf');
        }

        return view('dashboard.payments.receipt', ['payment' => $payment]);
    }

    public function generatePlan(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'parent') {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'plan' => ['required', Rule::in(['monthly', 'bi_monthly', 'triannual', 'yearly'])],
        ]);

        $allowedIds = DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->pluck('student_user_id')
            ->all();
        if (!in_array((int) $validated['student_id'], $allowedIds, true)) {
            abort(403);
        }

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

        if ($created > 0) {
            NotificationService::notifyStudentAndParents(
                $student->id,
                'Payment plan generated',
                $created.' tuition payment(s) were generated for '.$student->name.'.',
                'payment.plan.generated',
                ['student_id' => $student->id, 'plan' => $plan]
            );
        }

        $firstPayment = Payment::where('student_id', $student->id)
            ->where('invoice_no', 'like', 'TUITION-'.$student->id.'-'.$year.'-%')
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->first();

        if ($firstPayment) {
            $firstPayment->paid_amount = $firstPayment->amount;
            $firstPayment->paid_at = now();
            $firstPayment->status = 'paid';
            $firstPayment->save();

            NotificationService::notifyAdmins(
                'Payment received',
                $user->name.' paid invoice '.$firstPayment->invoice_no.' for '.$student->name.'.',
                'payment.paid',
                ['payment_id' => $firstPayment->id, 'student_id' => $student->id, 'paid_by_user_id' => $user->id]
            );
            NotificationService::notifyStudentAndParents(
                $student->id,
                'Payment completed',
                'Invoice '.$firstPayment->invoice_no.' for '.$student->name.' has been marked as paid.',
                'payment.paid',
                ['payment_id' => $firstPayment->id, 'student_id' => $student->id],
                true,
                true,
                [$user->id]
            );

            return redirect()->route('dashboard.payments.index', [
                'lang' => app()->getLocale(),
                'receipt' => $firstPayment->id,
            ])->with('success', $created.' payment(s) generated. First installment paid.');
        }

        return back()->with('success', $created.' payment(s) generated.');
    }

    private function resolveAppName(): string
    {
        return DB::table('app_settings')
            ->where('key', 'app_name')
            ->value('value') ?? 'Student Portal';
    }

    private function resolveAppLogoUrl(): ?string
    {
        $path = DB::table('app_settings')
            ->where('key', 'app_logo_path')
            ->value('value');

        return $path ? asset('storage/'.$path) : null;
    }
}
