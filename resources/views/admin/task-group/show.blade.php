@extends('admin.layouts.app')

@section('title', 'Chi tiết thẻ')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chi tiết thẻ</h2>

        <div class="card">
            <div class="card-body">
                <p><strong>ID:</strong> {{ $taskGroup->id }}</p>
                <p><strong>Tên:</strong> {{ $taskGroup->name }}</p>
                <p><strong>Người tạo:</strong>
                    {{ $taskGroup->user->email }}
                </p>
                <p><strong>Vai trò:</strong>
                    @if ($taskGroup->is_admin_created)
                    <span class="badge bg-danger">Admin</span>
                    @else
                        <span class="badge bg-primary">User</span>
                    @endif
                </p>
                
                <p><strong>Ngày tạo:</strong> {{ $taskGroup->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Cập nhật gần nhất:</strong> {{ $taskGroup->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <a href="{{ route('admin.task-groups.index') }}" class="btn btn-secondary mt-3">Quay lại danh sách</a>
        <a href="{{ route('admin.task-groups.edit', $taskGroup->id) }}" class="btn btn-warning mt-3">Chỉnh sửa</a>
    </div>
@endsection
