@extends('dashboard.layout')

@section('title', __('Notifications'))
@section('page_title', __('Notifications'))

@section('content')
<div class="page-actions">
    <div class="actions">
        <a class="{{ $filter === 'all' ? 'btn' : 'btn-outline' }}" href="{{ route('dashboard.notifications.index', ['lang' => app()->getLocale(), 'filter' => 'all']) }}">All</a>
        <a class="{{ $filter === 'unread' ? 'btn' : 'btn-outline' }}" href="{{ route('dashboard.notifications.index', ['lang' => app()->getLocale(), 'filter' => 'unread']) }}">Unread</a>
        <a class="{{ $filter === 'archived' ? 'btn' : 'btn-outline' }}" href="{{ route('dashboard.notifications.index', ['lang' => app()->getLocale(), 'filter' => 'archived']) }}">Archived</a>
    </div>
    <form method="POST" action="{{ route('dashboard.notifications.read-all', ['lang' => app()->getLocale()]) }}">
        @csrf
        <button type="submit" class="btn-outline">Mark All Read</button>
    </form>
</div>

<section class="card">
    <div style="display:flex; flex-direction:column; gap:.6rem;">
        @forelse ($notifications as $notification)
            <article class="card" style="margin:0; background:{{ $notification->read_at ? '#fff9df' : '#fff3bf' }}; border-style:dashed;">
                <div style="display:flex; justify-content:space-between; gap:.75rem; flex-wrap:wrap;">
                    <div style="min-width:0;">
                        <p style="margin:0; font-weight:800;">{{ $notification->title }}</p>
                        @if ($notification->body)
                            <p class="muted" style="margin:.25rem 0 0;">{{ $notification->body }}</p>
                        @endif
                        <p class="muted" style="margin:.3rem 0 0; font-size:.82rem;">{{ $notification->created_at?->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="actions">
                        @if ($notification->archived_at)
                            <form method="POST" action="{{ route('dashboard.notifications.unarchive', ['notification' => $notification, 'lang' => app()->getLocale()]) }}">
                                @csrf
                                <button type="submit" class="btn-outline">Unarchive</button>
                            </form>
                        @else
                            @if (!$notification->read_at)
                                <form method="POST" action="{{ route('dashboard.notifications.read', ['notification' => $notification, 'lang' => app()->getLocale()]) }}">
                                    @csrf
                                    <button type="submit" class="btn-outline">Mark Read</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('dashboard.notifications.archive', ['notification' => $notification, 'lang' => app()->getLocale()]) }}">
                                @csrf
                                <button type="submit" class="btn-outline">Archive</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('dashboard.notifications.destroy', ['notification' => $notification, 'lang' => app()->getLocale()]) }}" onsubmit="return confirm('Delete this notification?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="muted" style="margin:0;">No notifications yet.</p>
        @endforelse
    </div>

    <div class="pagination">{{ $notifications->links() }}</div>
</section>
@endsection
