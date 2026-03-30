<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $attendanceRate = Attendance::count() > 0
            ? round((Attendance::where('status', 'present')->count() / Attendance::count()) * 100)
            : 0;

        $pendingPayments = Payment::where('status', '!=', 'paid')->count();
        $pendingAbsences = Absence::where('verification_status', 'pending')->count();

        return view('dashboard.index', [
            'attendanceRate' => $attendanceRate,
            'pendingPayments' => $pendingPayments,
            'pendingAbsences' => $pendingAbsences,
            'totalStudents' => Student::count(),
            'recentAttendances' => Attendance::with('student')->latest()->take(5)->get(),
            'recentPayments' => Payment::with('student')->latest()->take(5)->get(),
            'recentAbsences' => Absence::with('student')->latest()->take(5)->get(),
        ]);
    }
}
