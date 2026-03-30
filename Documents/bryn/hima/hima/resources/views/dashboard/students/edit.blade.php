@extends('dashboard.layout')

@section('title', 'Edit Student')
@section('page_title', 'Edit Student')

@section('content')
<section class="card" style="max-width: 700px;">
    <form method="POST" action="{{ route('dashboard.students.update', ['student' => $student, 'lang' => app()->getLocale()]) }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="name">Student Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $student->name) }}" required>
            @error('name')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="class_name">Class</label>
            <input id="class_name" name="class_name" type="text" value="{{ old('class_name', $student->class_name) }}" required>
            @error('class_name')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="parent_name">Parent Name</label>
            <input id="parent_name" name="parent_name" type="text" value="{{ old('parent_name', $student->parent_name) }}" required>
            @error('parent_name')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="parent_contact">Parent Contact</label>
            <input id="parent_contact" name="parent_contact" type="text" value="{{ old('parent_contact', $student->parent_contact) }}" required>
            @error('parent_contact')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
            <button type="submit" class="btn">Update</button>
            <a class="btn-outline" href="{{ route('dashboard.students.index', ['lang' => app()->getLocale()]) }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
