<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConnectionsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $data = [
            'connections' => collect(),
            'role' => $user->role,
        ];

        if ($user->role === 'student') {
            $data['connections'] = DB::table('parent_student')
                ->join('users as parents', 'parents.id', '=', 'parent_student.parent_user_id')
                ->where('parent_student.student_user_id', $user->id)
                ->select('parents.name', 'parents.email')
                ->orderBy('parents.name')
                ->get();
        } elseif ($user->role === 'parent') {
            $data['connections'] = DB::table('parent_student')
                ->join('users as students', 'students.id', '=', 'parent_student.student_user_id')
                ->where('parent_student.parent_user_id', $user->id)
                ->select('students.name', 'students.email')
                ->orderBy('students.name')
                ->get();
        }

        return view('dashboard.connections.index', $data);
    }
}
