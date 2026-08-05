<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\TuitionProgram;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
        $query = Payment::with(['student', 'program'])->orderBy('due_date', 'asc');
        $students = User::where('role', 'student')->orderBy('name')->get();
        $studentCards = collect();
        $modalPayments = collect();
        $programs = TuitionProgram::query()->orderBy('name')->get();

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
        $studentProgramMap = collect();
        if ($isAdminView) {
            // student_id => list of enrolled program slugs (used to filter the Change plan form)
            foreach (DB::table('student_tuition_program')
                ->join('tuition_programs', 'tuition_programs.id', '=', 'student_tuition_program.tuition_program_id')
                ->get(['student_tuition_program.student_id', 'tuition_programs.slug']) as $row) {
                $studentProgramMap = $studentProgramMap->put($row->student_id, array_merge(
                    $studentProgramMap->get($row->student_id, []),
                    [$row->slug]
                ));
            }

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
            'programs' => $programs,
            'studentProgramMap' => $studentProgramMap,
            'plans' => TuitionProgram::PLANS,
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
            'programs' => TuitionProgram::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'tuition_program_id' => ['nullable', Rule::exists('tuition_programs', 'id')],
            'invoice_no' => ['required', 'string', 'max:255', 'unique:payments,invoice_no'],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:amount'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'partial', 'paid'])],
            'payment_method' => ['required', Rule::in(Payment::METHODS)],
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
            'programs' => TuitionProgram::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'tuition_program_id' => ['nullable', Rule::exists('tuition_programs', 'id')],
            'invoice_no' => ['required', 'string', 'max:255', 'unique:payments,invoice_no,' . $payment->id],
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'lte:amount'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'partial', 'paid'])],
            'payment_method' => ['required', Rule::in(Payment::METHODS)],
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
            'payment' => $payment->load('student', 'program'),
        ]);
    }

    /**
     * Set up (or change) the auto-generated installment plan for a student's
     * tuition program. Payments are generated per program, so a student
     * following multiple programs gets one set of payments per program.
     */
    public function changePlan(Request $request): RedirectResponse
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
            'tuition_program_id' => ['required', Rule::exists('tuition_programs', 'id')],
            'plan' => ['required', Rule::in(array_keys(TuitionProgram::PLANS))],
        ]);

        $student = User::findOrFail($validated['student_id']);
        $program = TuitionProgram::findOrFail($validated['tuition_program_id']);

        $plan = $validated['plan'];
        $config = TuitionProgram::PLANS[$plan];
        $count = $config['count'];
        $interval = $config['interval'];
        $year = now()->year;

        $perInstallment = $program->planPrice($plan);
        if ($perInstallment === null || $perInstallment <= 0) {
            // Fall back to the student's enrolled annual amount for this program.
            $enrollment = DB::table('student_tuition_program')
                ->where('student_id', $student->id)
                ->where('tuition_program_id', $program->id)
                ->first();
            $annual = $enrollment && $enrollment->annual_amount !== null
                ? (float) $enrollment->annual_amount
                : round((float) $program->monthly_amount * 12, 2);

            if ($annual <= 0) {
                return back()->withErrors(['plan' => 'No pricing set for this plan and program.']);
            }

            $perInstallment = round($annual / $count, 2);
        }

        $slug = $program->slug;
        $prefix = "TUITION-{$student->id}-{$slug}-{$year}-";

        // Changing the plan replaces previously auto-generated pending installments.
        Payment::where('student_id', $student->id)
            ->where('tuition_program_id', $program->id)
            ->where('status', 'pending')
            ->where('invoice_no', 'like', $prefix.'%')
            ->delete();

        $start = Carbon::now()->startOfMonth();
        $created = 0;

        for ($i = 1; $i <= $count; $i++) {
            $invoice = $prefix.$i;
            if (Payment::where('invoice_no', $invoice)->exists()) {
                continue;
            }

            Payment::create([
                'student_id' => $student->id,
                'tuition_program_id' => $program->id,
                'invoice_no' => $invoice,
                'amount' => $perInstallment,
                'paid_amount' => 0,
                'due_date' => $start->copy()->addMonths(($i - 1) * $interval)->toDateString(),
                'paid_at' => null,
                'status' => 'pending',
                'payment_method' => 'transfer',
            ]);
            $created++;
        }

        return back()->with('success', "{$program->name}: set up {$created} payment(s) for the {$config['label']} plan.");
    }
}
