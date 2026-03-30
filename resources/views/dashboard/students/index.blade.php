@extends('dashboard.layout')

@section('title', 'Students')
@section('page_title', 'Students')

@section('content')
<div class="page-actions">
    <button class="btn" type="button" data-modal-open="student-create-modal" @disabled($parents->isEmpty())>Add Student</button>
</div>

@if ($parents->isEmpty())
    <section class="card" style="margin-bottom:.8rem;">
        <p class="muted" style="margin:0;">No parent accounts found. Create/register a parent account first, then you can add students.</p>
    </section>
@endif

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
                            <button class="btn-outline" type="button" data-modal-open="student-edit-{{ $student->id }}">Edit</button>
                            <button class="btn btn-danger" type="button" data-modal-open="student-delete-{{ $student->id }}">Delete</button>
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

<div class="modal" id="student-create-modal">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-card">
        <div class="modal-head">
            <h2>Add Student</h2>
            <button class="btn-outline" type="button" data-modal-close>Close</button>
        </div>
        <form method="POST" action="{{ route('dashboard.students.store', ['lang' => app()->getLocale()]) }}">
            @csrf
            <div class="field">
                <label for="create-name">Student Name</label>
                <input id="create-name" name="name" type="text" required>
            </div>
            <div class="field">
                <label for="create-class">Class</label>
                <input id="create-class" name="class_name" type="text" required>
            </div>
            <div class="field">
                <label for="create-parent-user-id">Parent Account</label>
                <select id="create-parent-user-id" name="parent_user_id" class="js-parent-select" data-name-target="create-parent-name" data-contact-target="create-parent-contact" required>
                    <option value="">Select parent</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" data-parent-name="{{ $parent->name }}" data-parent-contact="{{ $parent->email }}">{{ $parent->name }} ({{ $parent->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="create-parent-name">Parent Name (auto)</label>
                <input id="create-parent-name" type="text" readonly>
            </div>
            <div class="field">
                <label for="create-parent-contact">Parent Contact (auto)</label>
                <input id="create-parent-contact" type="text" readonly>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Save</button>
            </div>
        </form>
    </div>
</div>

@foreach ($students as $student)
    <div class="modal" id="student-edit-{{ $student->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Edit Student</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <form method="POST" action="{{ route('dashboard.students.update', ['student' => $student, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="edit-name-{{ $student->id }}">Student Name</label>
                    <input id="edit-name-{{ $student->id }}" name="name" type="text" value="{{ $student->name }}" required>
                </div>
                <div class="field">
                    <label for="edit-class-{{ $student->id }}">Class</label>
                    <input id="edit-class-{{ $student->id }}" name="class_name" type="text" value="{{ $student->class_name }}" required>
                </div>
                <div class="field">
                    <label for="edit-parent-user-id-{{ $student->id }}">Parent Account</label>
                    <select id="edit-parent-user-id-{{ $student->id }}" name="parent_user_id" class="js-parent-select" data-name-target="edit-parent-name-{{ $student->id }}" data-contact-target="edit-parent-contact-{{ $student->id }}" required>
                        <option value="">Select parent</option>
                        @foreach ($parents as $parent)
                            <option
                                value="{{ $parent->id }}"
                                data-parent-name="{{ $parent->name }}"
                                data-parent-contact="{{ $parent->email }}"
                                @selected((int) ($student->parent_user_id ?? 0) === (int) $parent->id)
                            >
                                {{ $parent->name }} ({{ $parent->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="edit-parent-name-{{ $student->id }}">Parent Name (auto)</label>
                    <input id="edit-parent-name-{{ $student->id }}" type="text" value="{{ $student->parent_name }}" readonly>
                </div>
                <div class="field">
                    <label for="edit-parent-contact-{{ $student->id }}">Parent Contact (auto)</label>
                    <input id="edit-parent-contact-{{ $student->id }}" type="text" value="{{ $student->parent_contact }}" readonly>
                </div>
                <div class="actions">
                    <button type="submit" class="btn">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="student-delete-{{ $student->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <h2>Delete Student</h2>
                <button class="btn-outline" type="button" data-modal-close>Close</button>
            </div>
            <p>Delete <strong>{{ $student->name }}</strong>? This cannot be undone.</p>
            <form method="POST" action="{{ route('dashboard.students.destroy', ['student' => $student, 'lang' => app()->getLocale()]) }}">
                @csrf
                @method('DELETE')
                <div class="actions">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<script>
    (function () {
        function syncParentFields(select) {
            const selected = select.options[select.selectedIndex];
            const nameTarget = document.getElementById(select.dataset.nameTarget);
            const contactTarget = document.getElementById(select.dataset.contactTarget);

            if (!nameTarget || !contactTarget) return;

            nameTarget.value = selected ? (selected.dataset.parentName || '') : '';
            contactTarget.value = selected ? (selected.dataset.parentContact || '') : '';
        }

        document.querySelectorAll('.js-parent-select').forEach(function (select) {
            syncParentFields(select);
            select.addEventListener('change', function () {
                syncParentFields(select);
            });
        });
    })();
</script>
@endsection
