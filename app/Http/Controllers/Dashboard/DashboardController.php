<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:dashboard.view');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user && $user->role === 'student') {
            $attendanceQuery = Attendance::where('student_id', $user->id);
            $attendanceCount = $attendanceQuery->count();
            $attendanceRate = $attendanceCount > 0
                ? round((Attendance::where('student_id', $user->id)->where('status', 'present')->count() / $attendanceCount) * 100)
                : 0;

            $weekDate = $request->query('week', now()->toDateString());
            $weekStart = Carbon::parse($weekDate)->startOfWeek(Carbon::MONDAY);
            $weekEnd = (clone $weekStart)->endOfWeek(Carbon::SUNDAY);

            $weeklyRecords = Attendance::where('student_id', $user->id)
                ->whereBetween('attendance_date', [
                    $weekStart->toDateString(),
                    $weekEnd->toDateString(),
                ])
                ->get()
                ->keyBy(fn ($record) => $record->attendance_date->format('Y-m-d'));

            $connectionRequests = DB::table('parent_connection_requests')
                ->join('users as parents', 'parents.id', '=', 'parent_connection_requests.parent_user_id')
                ->where('parent_connection_requests.student_user_id', $user->id)
                ->where('parent_connection_requests.status', 'pending')
                ->select('parent_connection_requests.id', 'parents.name', 'parents.email', 'parent_connection_requests.created_at')
                ->orderByDesc('parent_connection_requests.created_at')
                ->get();
            $connectedParents = DB::table('parent_student')
                ->join('users as parents', 'parents.id', '=', 'parent_student.parent_user_id')
                ->where('parent_student.student_user_id', $user->id)
                ->select('parents.id', 'parents.name', 'parents.email')
                ->orderBy('parents.name')
                ->get();

            return view('dashboard.index', [
                'isStudent' => true,
                'attendanceRate' => $attendanceRate,
                'recentAttendances' => Attendance::where('student_id', $user->id)
                    ->latest('attendance_date')
                    ->take(10)
                    ->get(),
                'weekDate' => $weekDate,
                'weekStart' => $weekStart,
                'weekEnd' => $weekEnd,
                'weeklyRecords' => $weeklyRecords,
                'connectionRequests' => $connectionRequests,
                'connectedParents' => $connectedParents,
            ]);
        }

        if ($user && $user->role === 'parent') {
            $children = DB::table('parent_student')
                ->join('users as students', 'students.id', '=', 'parent_student.student_user_id')
                ->where('parent_student.parent_user_id', $user->id)
                ->select('students.id', 'students.name', 'students.email')
                ->orderBy('students.name')
                ->get();

            $missingPayments = 0;
            $daysUntilNextPaymentDue = null;
            if ($children->isNotEmpty()) {
                $today = now()->toDateString();
                $childIds = $children->pluck('id')->all();

                $missingPayments = Payment::query()
                    ->whereIn('student_id', $childIds)
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', $today)
                    ->where('status', '!=', 'paid')
                    ->count();

                $nextPaymentDue = Payment::query()
                    ->whereIn('student_id', $childIds)
                    ->whereNotNull('due_date')
                    ->where('status', '!=', 'paid')
                    ->orderBy('due_date')
                    ->first();

                if ($nextPaymentDue?->due_date) {
                    $daysUntil = now()->startOfDay()->diffInDays($nextPaymentDue->due_date->startOfDay(), false);
                    $daysUntilNextPaymentDue = max(0, $daysUntil);
                }
            }

            return view('dashboard.index', [
                'isStudent' => false,
                'isParent' => true,
                'children' => $children,
                'missingPayments' => $missingPayments,
                'daysUntilNextPaymentDue' => $daysUntilNextPaymentDue,
            ]);
        }

        $attendanceRate = Attendance::count() > 0
            ? round((Attendance::where('status', 'present')->count() / Attendance::count()) * 100)
            : 0;

        $pendingPayments = Payment::where('status', '!=', 'paid')->count();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $paidThisMonthStudentIds = Payment::query()
            ->select('student_id')
            ->whereNotNull('student_id')
            ->whereBetween('due_date', [$monthStart, $monthEnd])
            ->groupBy('student_id')
            ->havingRaw("SUM(CASE WHEN status != 'paid' THEN 1 ELSE 0 END) = 0")
            ->pluck('student_id');

        $unpaidStudentsThisMonth = User::query()
            ->where('role', 'student')
            ->whereNotIn('id', $paidThisMonthStudentIds)
            ->count();
        $pendingAbsences = Absence::where('verification_status', 'pending')->count();
        $monthlyIncome = (float) Payment::query()
            ->whereIn('status', ['paid', 'partial'])
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('paid_amount');

        return view('dashboard.index', [
            'isStudent' => false,
            'isParent' => false,
            'attendanceRate' => $attendanceRate,
            'pendingPayments' => $pendingPayments,
            'unpaidStudentsThisMonth' => $unpaidStudentsThisMonth,
            'pendingAbsences' => $pendingAbsences,
            'monthlyIncome' => $monthlyIncome,
            'totalStudents' => User::where('role', 'student')->count(),
            'recentAttendances' => Attendance::with('student')->latest()->paginate(5, ['*'], 'attendance_page'),
            'recentPayments' => Payment::with('student')->latest()->take(5)->get(),
            'recentAbsences' => Absence::with('student')->latest()->take(5)->get(),
        ]);
    }
}
