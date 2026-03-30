<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ActivityLogger
{
    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     */
    public static function log(string $action, string $subjectType, int $subjectId, string $description, ?array $before, ?array $after): Activity
    {
        $activity = Activity::create([
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
        ]);

        ActivityVersion::create([
            'activity_id' => $activity->id,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'action' => $action,
            'before' => $before,
            'after' => $after,
        ]);

        return $activity;
    }

    /**
     * @return array<string, mixed>
     */
    public static function snapshot(Model $model, string $subjectType): array
    {
        $data = $model->toArray();

        if ($subjectType === 'user') {
            $data = Arr::except($data, ['password', 'remember_token']);
        }

        return $data;
    }
}
