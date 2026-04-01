<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 401);

        $filter = (string) $request->query('filter', 'all');

        $query = UserNotification::query()
            ->where('user_id', $user->id);

        if ($filter === 'unread') {
            $query->whereNull('read_at')->whereNull('archived_at');
        } elseif ($filter === 'archived') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
            $filter = 'all';
        }

        $notifications = $query
            ->latest()
            ->paginate(15);

        return view('dashboard.notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
        ]);
    }

    public function read(Request $request, UserNotification $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $notification->user_id === $user->id, 403);

        if (!$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }

        return back();
    }

    public function archive(Request $request, UserNotification $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $notification->user_id === $user->id, 403);

        if (!$notification->archived_at) {
            $notification->archived_at = now();
            if (!$notification->read_at) {
                $notification->read_at = now();
            }
            $notification->save();
        }

        return back();
    }

    public function unarchive(Request $request, UserNotification $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $notification->user_id === $user->id, 403);

        if ($notification->archived_at) {
            $notification->archived_at = null;
            $notification->save();
        }

        return back();
    }

    public function destroy(Request $request, UserNotification $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $notification->user_id === $user->id, 403);

        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('archived_at')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $afterId = max(0, (int) $request->query('after_id', 0));

        $newNotifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('archived_at')
            ->where('id', '>', $afterId)
            ->latest('id')
            ->take(5)
            ->get()
            ->reverse()
            ->values()
            ->map(static fn (UserNotification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'created_at' => $notification->created_at?->format('Y-m-d H:i:s'),
            ]);

        $unreadCount = UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('archived_at')
            ->whereNull('read_at')
            ->count();

        $latestId = UserNotification::query()
            ->where('user_id', $user->id)
            ->max('id') ?? 0;

        return response()->json([
            'unread_count' => $unreadCount,
            'latest_id' => (int) $latestId,
            'notifications' => $newNotifications,
        ]);
    }
}
