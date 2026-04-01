@extends('dashboard.layout')

@section('title', 'Connections')
@section('page_title', 'Connections')

@section('content')
<section class="card">
    <h2 style="margin:.1rem 0 .6rem;font-size:1.05rem;">
        @if ($role === 'student')
            Connected Parents
        @elseif ($role === 'parent')
            Connected Students
        @else
            Connections
        @endif
    </h2>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($connections as $connection)
                <tr>
                    <td>{{ $connection->name }}</td>
                    <td>{{ $connection->email }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="muted">No connections yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
