@extends('admin.layouts.app')

@section('title', 'Chi tiết người dùng')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chi tiết người dùng</h2>

        <div class="card">
            <div class="card-body">
                <p><strong>ID:</strong> {{ $user->id }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Trạng thái:</strong>
                    @if ($user->status == 1)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Banned</span>
                    @endif
                </p>
                <p><strong>Ngày tạo:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Cập nhật gần nhất:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mt-3">Quay lại danh sách</a>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning mt-3">Chỉnh sửa</a>
    </div>
@endsection
