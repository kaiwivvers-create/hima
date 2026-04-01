@extends('dashboard.layout')

@section('title', __('dashboard.user_activity_title'))
@section('page_title', __('dashboard.user_activity_title'))

@section('content')
<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('dashboard.when') }}</th>
                <th>{{ __('dashboard.actor') }}</th>
                <th>{{ __('dashboard.action') }}</th>
                <th>{{ __('dashboard.description') }}</th>
                <th>{{ __('dashboard.view') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($activities as $activity)
                <tr>
                    <td>{{ $activity->created_at->format('M j, Y g:i A') }}</td>
                    <td>
                        {{ $activity->actor?->name ?? __('dashboard.system') }}
                        @if ($activity->actor)
                            <div class="muted" style="font-size:.85rem;">{{ $activity->actor->email }}</div>
                        @endif
                    </td>
                    <td>{{ $activity->action }}</td>
                    <td>{{ $activity->description ?? '-' }}</td>
                    <td>
                        <button class="btn-outline" type="button" data-modal-open="activity-view-{{ $activity->id }}">{{ __('dashboard.view') }}</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">{{ __('dashboard.no_activity') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $activities->withQueryString()->links() }}</div>
</section>

@foreach ($activities as $activity)
    @php
        $subject = $subjects[$activity->id] ?? null;
        $canRevertNow = $canRevert[$activity->id] ?? false;
        $canPurgeNow = $canPurge[$activity->id] ?? false;
        $metadata = $activity->metadata ? json_encode($activity->metadata, JSON_PRETTY_PRINT) : null;
        $versionsForSubject = $allVersions[$activity->subject_type.':'.$activity->subject_id] ?? collect();
    @endphp
    <div class="modal" id="activity-view-{{ $activity->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>{{ __('dashboard.activity_detail') }}</h2>
                <button class="btn-outline" type="button" data-modal-close>{{ __('dashboard.close') }}</button>
            </div>
            <div class="grid">
                <div class="card" style="grid-column: span 6;">
                    <p class="muted" style="margin:0 0 .35rem;">{{ __('dashboard.when') }}</p>
                    <p style="margin:0;font-weight:700;">{{ $activity->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="card" style="grid-column: span 6;">
                    <p class="muted" style="margin:0 0 .35rem;">{{ __('dashboard.actor') }}</p>
                    <p style="margin:0;font-weight:700;">{{ $activity->actor?->name ?? __('dashboard.system') }}</p>
                    @if ($activity->actor)
                        <p class="muted" style="margin:.2rem 0 0;">{{ $activity->actor->email }}</p>
                    @endif
                </div>
                <div class="card" style="grid-column: span 6;">
                    <p class="muted" style="margin:0 0 .35rem;">{{ __('dashboard.action') }}</p>
                    <p style="margin:0;font-weight:700;">{{ $activity->action }}</p>
                </div>
                <div class="card" style="grid-column: span 6;">
                    <p class="muted" style="margin:0 0 .35rem;">{{ __('dashboard.subject') }}</p>
                    @if ($subject)
                        <p style="margin:0;font-weight:700;">
                            {{ $activity->subject_type }} #{{ $activity->subject_id }}
                        </p>
                        <p class="muted" style="margin:.2rem 0 0;">
                            {{ $subject->name ?? $subject->email ?? 'Unknown' }}
                            @if (method_exists($subject, 'trashed') && $subject->trashed())
                                ({{ __('dashboard.deleted') }})
                            @endif
                        </p>
                    @else
                        <p class="muted" style="margin:0;">{{ __('dashboard.no_subject') }}</p>
                    @endif
                </div>
                <div class="card" style="grid-column: span 12;">
                    <p class="muted" style="margin:0 0 .35rem;">{{ __('dashboard.description') }}</p>
                    <p style="margin:0;">{{ $activity->description ?? '-' }}</p>
                </div>
                <div class="card" style="grid-column: span 12;">
                    <p class="muted" style="margin:0 0 .35rem;">{{ __('dashboard.metadata') }}</p>
                    @if ($metadata)
                        <pre style="margin:0;white-space:pre-wrap;">{{ $metadata }}</pre>
                    @else
                        <p class="muted" style="margin:0;">{{ __('dashboard.no_metadata') }}</p>
                    @endif
                </div>
                <div class="card" style="grid-column: span 12;">
                    <p class="muted" style="margin:0 0 .35rem;">{{ __('dashboard.version_history') }}</p>
                    @if ($versionsForSubject->isEmpty())
                        <p class="muted" style="margin:0;">{{ __('dashboard.no_versions') }}</p>
                    @else
                        <table class="table" style="margin-top:.35rem;">
                            <thead>
                                <tr>
                                    <th>{{ __('dashboard.when') }}</th>
                                    <th>{{ __('dashboard.action') }}</th>
                                    <th>{{ __('dashboard.revert_to') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($versionsForSubject as $version)
                                    @php
                                        $canRevertVersion = $version->after || $version->before;
                                    @endphp
                                    <tr>
                                        <td>{{ $version->created_at->format('M j, Y g:i A') }}</td>
                                        <td>{{ $version->action }}</td>
                                        <td>
                                            @if ($canRevertVersion)
                                                <form method="POST" action="{{ route('dashboard.admin.activities.versions.revert', ['version' => $version, 'lang' => app()->getLocale()]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-outline">{{ __('dashboard.revert') }}</button>
                                                </form>
                                            @else
                                                <span class="muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="actions" style="margin-top:.8rem;">
                @if ($canRevertNow)
                    <form method="POST" action="{{ route('dashboard.admin.activities.revert', ['activity' => $activity, 'lang' => app()->getLocale()]) }}">
                        @csrf
                        <button type="submit" class="btn">{{ __('dashboard.revert') }}</button>
                    </form>
                @endif
                @if ($canPurgeNow)
                    <form method="POST" action="{{ route('dashboard.admin.activities.purge', ['activity' => $activity, 'lang' => app()->getLocale()]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('dashboard.permanently_delete') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endforeach
@endsection
