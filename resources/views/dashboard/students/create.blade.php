@extends('dashboard.layout')

@section('title', 'Create Student')
@section('page_title', 'Create Student')

@section('content')
<section class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('dashboard.students.store', ['lang' => app()->getLocale()]) }}">
        @csrf

        <div class="field">
            <label for="name">Student Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            @error('name')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="class_name">Class</label>
            <input id="class_name" name="class_name" type="text" value="{{ old('class_name') }}" required>
            @error('class_name')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="parent_user_id">Parent Account</label>
            <select id="parent_user_id" name="parent_user_id" class="js-parent-select" data-name-target="parent_name_preview" data-contact-target="parent_contact_preview" required>
                <option value="">Select parent</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" data-parent-name="{{ $parent->name }}" data-parent-contact="{{ $parent->email }}" @selected((string) old('parent_user_id') === (string) $parent->id)>
                        {{ $parent->name }} ({{ $parent->email }})
                    </option>
                @endforeach
            </select>
            @error('parent_user_id')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="parent_name_preview">Parent Name (auto)</label>
            <input id="parent_name_preview" type="text" readonly>
        </div>

        <div class="field">
            <label for="parent_contact_preview">Parent Contact (auto)</label>
            <input id="parent_contact_preview" type="text" readonly>
        </div>

        <div class="actions">
            <button type="submit" class="btn">Save</button>
            <a class="btn-outline" href="{{ route('dashboard.students.index', ['lang' => app()->getLocale()]) }}">Cancel</a>
        </div>
    </form>
</section>

<script>
    (function () {
        const select = document.querySelector('.js-parent-select');
        if (!select) return;

        const nameInput = document.getElementById(select.dataset.nameTarget);
        const contactInput = document.getElementById(select.dataset.contactTarget);

        function sync() {
            const option = select.options[select.selectedIndex];
            nameInput.value = option ? (option.dataset.parentName || '') : '';
            contactInput.value = option ? (option.dataset.parentContact || '') : '';
        }

        sync();
        select.addEventListener('change', sync);
    })();
</script>
@endsection
