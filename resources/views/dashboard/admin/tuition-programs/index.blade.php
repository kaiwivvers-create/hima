@extends('dashboard.layout')

@section('title', 'Tuition Programs')
@section('page_title', 'Tuition Programs')

@section('content')
<section class="card" style="margin-bottom:.8rem;">
    <h2 style="margin:.1rem 0 .6rem;font-size:1.05rem;">Create Program</h2>
    <form method="POST" action="{{ route('dashboard.admin.tuition-programs.store', ['lang' => app()->getLocale()]) }}" class="grid">
        @csrf
        <div class="field" style="grid-column: span 4; margin:0;">
            <label for="program-name">Name</label>
            <input id="program-name" name="name" type="text" required>
        </div>
        <div class="field" style="grid-column: span 4; margin:0;">
            <label for="program-slug">Slug (optional)</label>
            <input id="program-slug" name="slug" type="text" placeholder="english-plus">
        </div>
        <div class="field" style="grid-column: span 4; margin:0;">
            <label for="program-monthly-amount">Monthly Price</label>
            <input id="program-monthly-amount" name="monthly_amount" type="number" min="0" step="0.01" required>
        </div>
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
                <th>Monthly Price</th>
                <th>Annual Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($programs as $program)
                <tr>
                    <td>{{ $program->name }}</td>
                    <td>{{ $program->slug }}</td>
                    <td>{{ number_format((float) $program->monthly_amount, 2) }}</td>
                    <td>{{ number_format((float) $program->monthly_amount * 12, 2) }}</td>
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
                    <td colspan="5" class="muted">No tuition programs yet.</td>
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
                <div class="field">
                    <label for="edit-program-monthly-{{ $program->id }}">Monthly Price</label>
                    <input id="edit-program-monthly-{{ $program->id }}" name="monthly_amount" type="number" min="0" step="0.01" value="{{ $program->monthly_amount }}" required>
                </div>
                <div class="actions">
                    <button type="submit" class="btn">Update</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection

