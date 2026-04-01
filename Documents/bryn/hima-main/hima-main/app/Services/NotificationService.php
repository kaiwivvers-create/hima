<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public static function notifyUser(int|User|null $user, string $title, ?string $body = null, ?string $type = null, ?array $data = null): void
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        self::createNotifications($user ? [(int) $user] : [], $title, $body, $type, $data);
    }

    /**
     * @param  array<int, int|User>  $users
     */
    public static function notifyUsers(array $users, string $title, ?string $body = null, ?string $type = null, ?array $data = null): void
    {
        self::createNotifications(self::normalizeUserIds($users), $title, $body, $type, $data);
    }

    /**
     * @param  string|array<int, string>  $roles
     */
    public static function notifyRoles(string|array $roles, string $title, ?string $body = null, ?string $type = null, ?array $data = null): void
    {
        $roleList = array_values(array_filter((array) $roles));
        if ($roleList === []) {
            return;
        }

        $userIds = User::query()
            ->whereIn('role', $roleList)
            ->pluck('id')
            ->all();

        self::createNotifications($userIds, $title, $body, $type, $data);
    }

    public static function notifyAdmins(string $title, ?string $body = null, ?string $type = null, ?array $data = null): void
    {
        self::notifyRoles(['admin', 'super admin'], $title, $body, $type, $data);
    }

    /**
     * @param  int|User  $student
     * @param  array<int, int>  $excludeUserIds
     */
    public static function notifyStudentAndParents(
        int|User $student,
        string $title,
        ?string $body = null,
        ?string $type = null,
        ?array $data = null,
        bool $includeStudent = true,
        bool $includeParents = true,
        array $excludeUserIds = []
    ): void {
        $studentId = $student instanceof User ? $student->id : (int) $student;
        if (!$studentId) {
            return;
        }

        $userIds = [];

        if ($includeStudent) {
            $userIds[] = $studentId;
        }

        if ($includeParents) {
            $parentIds = DB::table('parent_student')
                ->where('student_user_id', $studentId)
                ->pluck('parent_user_id')
                ->all();

            $userIds = [...$userIds, ...$parentIds];
        }

        if ($excludeUserIds !== []) {
            $excludeUserIds = array_map('intval', $excludeUserIds);
            $userIds = array_values(array_diff($userIds, $excludeUserIds));
        }

        self::createNotifications($userIds, $title, $body, $type, $data);
    }

    /**
     * @param  array<int, int|User>  $users
     * @return array<int, int>
     */
    private static function normalizeUserIds(array $users): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (int|User|null $user): ?int => $user instanceof User ? $user->id : ($user ? (int) $user : null),
            $users
        ))));
    }

    /**
     * @param  array<int, int>  $userIds
     */
    private static function createNotifications(array $userIds, string $title, ?string $body, ?string $type, ?array $data): void
    {
        $userIds = self::normalizeUserIds($userIds);
        if ($userIds === []) {
            return;
        }

        $existingUserIds = User::query()
            ->whereIn('id', $userIds)
            ->pluck('id')
            ->all();

        if ($existingUserIds === []) {
            return;
        }

        $now = now();

        UserNotification::insert(array_map(
            static fn (int $userId): array => [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $existingUserIds
        ));
    }
}
