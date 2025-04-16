@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Quản lý người dùng</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Thêm người dùng</a>
    </div>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Email</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $key => $user)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if ($user->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Banned</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning">Sửa</a>

                        <!-- Ban/Unban -->
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#banUserModal"
                            data-userid="{{ $user->id }}" data-userstatus="{{ $user->status }}">
                            {{ $user->status === 'active' ? 'Ban' : 'Unban' }}
                        </button>

                        <!-- Delete -->
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                            data-userid="{{ $user->id }}">
                            Xóa
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteUserForm">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Xác nhận xóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Bạn có chắc chắn muốn xóa người dùng này không?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xóa</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ban Confirmation Modal -->
    <div class="modal fade" id="banUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="banUserForm">
                @csrf
                @method('PATCH')
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Xác nhận thay đổi trạng thái</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="banModalMessage">
                        Bạn có chắc chắn muốn thay đổi trạng thái người dùng?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning">Xác nhận</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for modal dynamic form actions -->
    <script>
        const deleteModal = document.getElementById('deleteUserModal');
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-userid');
            const form = document.getElementById('deleteUserForm');
            form.action = `/admin/users/${userId}`;
        });

        const banModal = document.getElementById('banUserModal');
        banModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-userid');
            const status = button.getAttribute('data-userstatus');
            const form = document.getElementById('banUserForm');
            const msg = document.getElementById('banModalMessage');

            form.action = `/admin/users/${userId}/ban`;
            msg.textContent = status === 'active' ?
                'Bạn có chắc muốn ban người dùng này không?' :
                'Bạn có muốn mở khóa người dùng này không?';
        });
    </script>
@endsection
