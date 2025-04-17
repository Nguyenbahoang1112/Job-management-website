@extends('admin.layouts.app')

@section('title', 'Chi tiết thẻ')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chi tiết thẻ</h2>

        <div class="card">
            <div class="card-body">
                <p><strong>ID:</strong> {{ $tag->id }}</p>
                <p><strong>Tên:</strong> {{ $tag->name }}</p>
                <p><strong>Người tạo:</strong>
                    {{ $tag->user->name }}
                </p>
                <p><strong>Vai trò:</strong>
                    @if ($tag->is_admin_created)
                    <span class="badge bg-danger">Admin</span>
                    @else
                        <span class="badge bg-primary">User</span>
                    @endif
                </p>
                
                <p><strong>Ngày tạo:</strong> {{ $tag->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Cập nhật gần nhất:</strong> {{ $tag->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary mt-3">Quay lại danh sách</a>
        <a href="{{ route('admin.tags.edit', $tag->id) }}" class="btn btn-warning mt-3">Chỉnh sửa</a>
    </div>
@endsection
