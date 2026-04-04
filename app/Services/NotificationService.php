<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;

class NotificationService
{
    public function syncAdminPaymentReminders(User $user): void
    {
        if (in_array($user->role, ['parent', 'student'], true)) {
            return;
        }

        $today = now()->startOfDay();

        if ($user->role === 'teacher') {
            $this->createPendingAbsenceReminders($user, $today);

            return;
        }

        if (!in_array($user->role, ['admin', 'super admin'], true)) {
            return;
        }

        $this->createMonthlyUnpaidReminder($user, $today);
        $this->createDueDateReminders($user, $today);
    }

    private function createPendingAbsenceReminders(User $user, Carbon $today): void
    {
        $pendingAbsences = Absence::query()
            ->with('student:id,name,email')
            ->where('verification_status', 'pending')
            ->latest('start_date')
            ->get();

        foreach ($pendingAbsences as $absence) {
            $studentName = $absence->student?->name ?? __('Student');
            $startDate = $absence->start_date?->format('Y-m-d') ?? '-';
            $endDate = $absence->end_date?->format('Y-m-d') ?? '-';

            $this->createDailyUniqueNotification(
                $user,
                'absence.pending.a'.$absence->id,
                __('Absence note pending review'),
                __(':student submitted an absence note for :start to :end.', [
                    'student' => $studentName,
                    'start' => $startDate,
                    'end' => $endDate,
                ]),
                [
                    'absence_id' => $absence->id,
                    'student_id' => $absence->student_id,
                    'student_name' => $studentName,
                    'start_date' => $absence->start_date?->toDateString(),
                    'end_date' => $absence->end_date?->toDateString(),
                    'verification_status' => $absence->verification_status,
                ],
                $today
            );
        }
    }

    private function createDueDateReminders(User $user, Carbon $today): void
    {
        $payments = Payment::query()
            ->with('student:id,name,email')
            ->where('status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->get();

        foreach ($payments as $payment) {
            $dueDate = Carbon::parse($payment->due_date)->startOfDay();
            $daysUntil = $today->diffInDays($dueDate, false);

            if (in_array($daysUntil, [3, 2, 1], true)) {
                $key = 'due_in_'.$daysUntil;
                $title = __('Payment due in :count day(s)', ['count' => $daysUntil]);
                $body = __(':student has invoice :invoice due on :date.', [
                    'student' => $payment->student?->name ?? __('Student'),
                    'invoice' => $payment->invoice_no,
                    'date' => $dueDate->format('Y-m-d'),
                ]);
            } elseif ($daysUntil === 0) {
                $key = 'due_today';
                $title = __('Payment due today');
                $body = __(':student has invoice :invoice due today.', [
                    'student' => $payment->student?->name ?? __('Student'),
                    'invoice' => $payment->invoice_no,
                ]);
            } elseif ($daysUntil < 0) {
                $key = 'overdue';
                $overdueDays = abs($daysUntil);
                $title = __('Payment overdue');
                $body = __(':student has invoice :invoice overdue by :count day(s).', [
                    'student' => $payment->student?->name ?? __('Student'),
                    'invoice' => $payment->invoice_no,
                    'count' => $overdueDays,
                ]);
            } else {
                continue;
            }

            $this->createDailyUniqueNotification(
                $user,
                'payment.reminder.p'.$payment->id.'.'.$key,
                $title,
                $body,
                [
                    'payment_id' => $payment->id,
                    'student_id' => $payment->student_id,
                    'invoice_no' => $payment->invoice_no,
                    'reminder_key' => $key,
                    'due_date' => $dueDate->toDateString(),
                ],
                $today
            );
        }
    }

    private function createMonthlyUnpaidReminder(User $user, Carbon $today): void
    {
        $monthStart = $today->copy()->startOfMonth()->toDateString();
        $monthEnd = $today->copy()->endOfMonth()->toDateString();

        $paidThisMonthStudentIds = Payment::query()
            ->select('student_id')
            ->whereNotNull('student_id')
            ->whereBetween('due_date', [$monthStart, $monthEnd])
            ->groupBy('student_id')
            ->havingRaw("SUM(CASE WHEN status != 'paid' THEN 1 ELSE 0 END) = 0")
            ->pluck('student_id');

        $unpaidStudents = User::query()
            ->where('role', 'student')
            ->whereNotIn('id', $paidThisMonthStudentIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        foreach ($unpaidStudents as $student) {
            $this->createDailyUniqueNotification(
                $user,
                'payment.monthly-unpaid.student.'.$student->id,
                __('Student not paid this month'),
                __(':student has not completed payment for :month.', [
                    'student' => $student->name,
                    'month' => $today->format('F Y'),
                ]),
                [
                    'month' => $today->format('Y-m'),
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'student_email' => $student->email,
                ],
                $today
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createDailyUniqueNotification(
        User $user,
        string $type,
        string $title,
        string $body,
        array $data,
        Carbon $today
    ): void {
        $exists = UserNotification::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereDate('created_at', $today->toDateString())
            ->exists();

        if ($exists) {
            return;
        }

        UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'read_at' => null,
        ]);
    }
}
