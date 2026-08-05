@extends('dashboard.layout')

@section('title', 'Tuition Programs')
@section('page_title', 'Tuition Programs')

@section('content')
<section class="card" style="margin-bottom:.8rem;">
    <h2 style="margin:.1rem 0 .6rem;font-size:1.05rem;">Create Program</h2>
    <p class="muted" style="margin:0 0 .6rem;font-size:.88rem;">
        Set the price for each billing plan. Leave a plan's price empty to hide it for this program
        (e.g. a program that bills 4x a year instead of 3x a year).
    </p>
    <form method="POST" action="{{ route('dashboard.admin.tuition-programs.store', ['lang' => app()->getLocale()]) }}" class="grid">
        @csrf
        <div class="field" style="grid-column: span 6; margin:0;">
            <label for="program-name">Name</label>
            <input id="program-name" name="name" type="text" required>
        </div>
        <div class="field" style="grid-column: span 6; margin:0;">
            <label for="program-slug">Slug (optional)</label>
            <input id="program-slug" name="slug" type="text" placeholder="english-plus">
        </div>
        @foreach ($plans as $key => $plan)
            <div class="field" style="grid-column: span 4; margin:0;">
                <label for="program-{{ $key }}-amount">{{ $plan['label'] }} — price per payment</label>
                <input id="program-{{ $key }}-amount" name="{{ $plan['column'] }}" type="number" min="0" step="0.01" placeholder="Leave empty to disable">
            </div>
        @endforeach
        <div class="actions" style="grid-column: span 12; justify-content:flex-end;">
            <button type="submit" class="btn">Create Program</button>
        </div>
    </form>
</section>

<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Monthly (12x)</th>
                <th>Every 2 mo (6x)</th>
                <th>3x per year</th>
                <th>4x per year</th>
                <th>Yearly (1x)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($programs as $program)
                <tr>
                    <td>{{ $program->name }}</td>
                    <td>{{ $program->slug }}</td>
                    <td>{{ $program->monthly_amount !== null ? number_format((float) $program->monthly_amount, 2) : '-' }}</td>
                    <td>{{ $program->bi_monthly_amount !== null ? number_format((float) $program->bi_monthly_amount, 2) : '-' }}</td>
                    <td>{{ $program->triannual_amount !== null ? number_format((float) $program->triannual_amount, 2) : '-' }}</td>
                    <td>{{ $program->quarterly_amount !== null ? number_format((float) $program->quarterly_amount, 2) : '-' }}</td>
                    <td>{{ $program->yearly_amount !== null ? number_format((float) $program->yearly_amount, 2) : '-' }}</td>
                    <td>
                        <div class="actions">
                            <button class="btn-outline" type="button" data-modal-open="program-edit-{{ $program->id }}">Edit</button>
                            <form method="POST" action="{{ route('dashboard.admin.tuition-programs.destroy', ['program' => $program, 'lang' => app()->getLocale()]) }}" onsubmit="return confirm('Delete this tuition program?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">No tuition programs yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

@foreach ($programs as $program)
    <div class="modal" id="program-edit-{{ $program->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Edit Tuition Program</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <form method="POST" action="{{ route('dashboard.admin.tuition-programs.update', ['program' => $program, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="edit-program-name-{{ $program->id }}">Name</label>
                    <input id="edit-program-name-{{ $program->id }}" name="name" type="text" value="{{ $program->name }}" required>
                </div>
                <div class="field">
                    <label for="edit-program-slug-{{ $program->id }}">Slug</label>
                    <input id="edit-program-slug-{{ $program->id }}" name="slug" type="text" value="{{ $program->slug }}" required>
                </div>
                <div class="grid">
                    @foreach ($plans as $key => $plan)
                        <div class="field" style="grid-column: span 6; margin:0;">
                            <label for="edit-program-{{ $key }}-{{ $program->id }}">{{ $plan['label'] }} — price per payment</label>
                            <input id="edit-program-{{ $key }}-{{ $program->id }}" name="{{ $plan['column'] }}" type="number" min="0" step="0.01" value="{{ $program->{$plan['column']} }}" placeholder="Leave empty to disable">
                        </div>
                    @endforeach
                </div>
                <div class="actions" style="margin-top:.8rem;">
                    <button type="submit" class="btn">Update</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection
