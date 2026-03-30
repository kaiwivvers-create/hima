@extends('dashboard.layout')

@section('title', 'Students')
@section('page_title', 'Students')

@section('content')
<div class="page-actions">
    <a class="btn" href="{{ route('dashboard.students.create', ['lang' => app()->getLocale()]) }}">Add Student</a>
</div>

<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Class</th>
                <th>Parent Name</th>
                <th>Parent Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->class_name }}</td>
                    <td>{{ $student->parent_name }}</td>
                    <td>{{ $student->parent_contact }}</td>
                    <td>
                        <div class="actions">
                            <a class="btn-outline" href="{{ route('dashboard.students.edit', ['student' => $student, 'lang' => app()->getLocale()]) }}">Edit</a>
                            <form method="POST" action="{{ route('dashboard.students.destroy', ['student' => $student, 'lang' => app()->getLocale()]) }}" onsubmit="return confirm('Delete this student?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $students->withQueryString()->links() }}</div>
</section>
@endsection
