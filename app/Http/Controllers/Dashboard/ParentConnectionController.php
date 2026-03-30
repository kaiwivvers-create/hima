<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentConnectionController extends Controller
{
    public function request(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'parent') {
            abort(403);
        }

        $validated = $request->validate([
            'student_email' => ['required', 'email'],
        ]);

        $student = User::where('email', $validated['student_email'])
            ->where('role', 'student')
            ->first();

        if (!$student) {
            return back()->withErrors(['student_email' => 'Student email not found.']);
        }

        $exists = DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->where('student_user_id', $student->id)
            ->exists();
        if ($exists) {
            return back()->with('success', 'Student already connected.');
        }

        DB::table('parent_connection_requests')->updateOrInsert(
            [
                'parent_user_id' => $user->id,
                'student_user_id' => $student->id,
            ],
            [
                'status' => 'pending',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        ActivityLogger::log(
            'parent_connection.requested',
            'user',
            $user->id,
            'Parent connection requested: '.$student->email,
            null,
            null
        );

        return back()->with('success', 'Connection request sent.');
    }

    public function accept(Request $request, int $requestId): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'student') {
            abort(403);
        }

        $connectionRequest = DB::table('parent_connection_requests')->where('id', $requestId)->first();
        if (!$connectionRequest || (int) $connectionRequest->student_user_id !== (int) $user->id) {
            abort(403);
        }

        DB::transaction(function () use ($connectionRequest) {
            DB::table('parent_connection_requests')
                ->where('id', $connectionRequest->id)
                ->update([
                    'status' => 'accepted',
                    'updated_at' => now(),
                ]);

            DB::table('parent_student')->updateOrInsert(
                [
                    'parent_user_id' => $connectionRequest->parent_user_id,
                    'student_user_id' => $connectionRequest->student_user_id,
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });

        ActivityLogger::log(
            'parent_connection.accepted',
            'user',
            $user->id,
            'Parent connection accepted.',
            null,
            null
        );

        return back()->with('success', 'Connection accepted.');
    }

    public function reject(Request $request, int $requestId): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'student') {
            abort(403);
        }

        $connectionRequest = DB::table('parent_connection_requests')->where('id', $requestId)->first();
        if (!$connectionRequest || (int) $connectionRequest->student_user_id !== (int) $user->id) {
            abort(403);
        }

        DB::table('parent_connection_requests')
            ->where('id', $connectionRequest->id)
            ->update([
                'status' => 'rejected',
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Connection rejected.');
    }
}
