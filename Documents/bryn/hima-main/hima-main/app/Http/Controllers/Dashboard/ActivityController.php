<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Activity;
use App\Models\ActivityVersion;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin.activities.view');
    }

    public function index(): View
    {
        $activities = Activity::with('actor')->latest()->paginate(15);
        $subjects = [];
        $canRevert = [];
        $canPurge = [];

        foreach ($activities as $activity) {
            $subject = $this->resolveSubject($activity);
            $subjects[$activity->id] = $subject;

            $isDelete = in_array($activity->action, ['student.deleted', 'user.deleted'], true);
            $trashed = $subject && method_exists($subject, 'trashed') && $subject->trashed();

            $canRevert[$activity->id] = $isDelete && $trashed;
            $canPurge[$activity->id] = $isDelete && $trashed;
        }

        $versionGroups = ActivityVersion::whereIn('activity_id', $activities->pluck('id'))
            ->get()
            ->groupBy('activity_id');

        return view('dashboard.admin.activities.index', [
            'activities' => $activities,
            'subjects' => $subjects,
            'canRevert' => $canRevert,
            'canPurge' => $canPurge,
            'versionGroups' => $versionGroups,
            'allVersions' => ActivityVersion::whereIn('subject_id', $activities->pluck('subject_id')->all())
                ->whereIn('subject_type', $activities->pluck('subject_type')->all())
                ->orderByDesc('created_at')
                ->get()
                ->groupBy(fn ($version) => $version->subject_type.':'.$version->subject_id),
        ]);
    }

    public function revert(Activity $activity): RedirectResponse
    {
        $subject = $this->resolveSubject($activity);
        if (!$subject || !method_exists($subject, 'trashed') || !$subject->trashed()) {
            return back()->with('success', 'Nothing to revert.');
        }

        if ($subject instanceof User && $subject->id === auth()->id()) {
            return back()->with('success', 'Cannot restore the current user from here.');
        }

        $subject->restore();
        $this->logActivity(
            $subject instanceof Student ? 'student.restored' : 'user.restored',
            'Restored deleted '.$activity->subject_type.': '.($subject->name ?? $subject->email ?? 'unknown'),
            $subject
        );

        return back()->with('success', 'Item restored.');
    }

    public function purge(Activity $activity): RedirectResponse
    {
        $subject = $this->resolveSubject($activity);
        if (!$subject || !method_exists($subject, 'trashed') || !$subject->trashed()) {
            return back()->with('success', 'Nothing to permanently delete.');
        }

        if ($subject instanceof User && $subject->id === auth()->id()) {
            return back()->with('success', 'Cannot permanently delete the current user from here.');
        }

        $label = $subject->name ?? $subject->email ?? 'unknown';
        $subject->forceDelete();

        $this->logActivity(
            $subject instanceof Student ? 'student.purged' : 'user.purged',
            'Permanently deleted '.$activity->subject_type.': '.$label,
            $subject
        );

        return back()->with('success', 'Item permanently deleted.');
    }

    public function revertVersion(ActivityVersion $version): RedirectResponse
    {
        $snapshot = $version->after ?? $version->before;
        if (!$snapshot) {
            return back()->with('success', 'Nothing to revert for this version.');
        }

        $subject = $this->resolveSubjectByType($version->subject_type, $version->subject_id);

        if ($subject && method_exists($subject, 'trashed') && $subject->trashed()) {
            $subject->restore();
        }

        $modelClass = $this->subjectClass($version->subject_type);
        if (!$modelClass) {
            return back()->with('success', 'Unsupported subject type.');
        }

        $modelClass::unguard();
        $modelClass::updateOrCreate(['id' => $snapshot['id']], $snapshot);
        $modelClass::reguard();

        return back()->with('success', 'Reverted to selected version.');
    }

    private function resolveSubject(Activity $activity): User|Student|Attendance|Payment|Absence|null
    {
        if (!$activity->subject_type || !$activity->subject_id) {
            return null;
        }

        return $this->resolveSubjectByType($activity->subject_type, $activity->subject_id);
    }

    private function logActivity(string $action, string $description, User|Student $subject): void
    {
        Activity::create([
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject instanceof Student ? 'student' : 'user',
            'subject_id' => $subject->id,
            'description' => $description,
        ]);
    }

    private function resolveSubjectByType(string $type, int $id): User|Student|Attendance|Payment|Absence|null
    {
        return match ($type) {
            'user' => User::withTrashed()->find($id),
            'student' => Student::withTrashed()->find($id),
            'attendance' => Attendance::find($id),
            'payment' => Payment::find($id),
            'absence' => Absence::find($id),
            default => null,
        };
    }

    private function subjectClass(string $type): ?string
    {
        return match ($type) {
            'user' => User::class,
            'student' => Student::class,
            'attendance' => \App\Models\Attendance::class,
            'payment' => \App\Models\Payment::class,
            'absence' => \App\Models\Absence::class,
            default => null,
        };
    }
}
