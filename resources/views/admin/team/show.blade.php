@extends('admin.layouts.app')

@section('title', 'Chi tiết thẻ')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chi tiết thẻ</h2>

        <div class="card">
            <div class="card-body">
                <p><strong>ID:</strong> {{ $team->id }}</p>
                <p><strong>Tên:</strong> {{ $team->name }}</p>
                <p><strong>Ngày tạo:</strong> {{ $team->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Cập nhật gần nhất:</strong> {{ $team->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary mt-3">Quay lại danh sách</a>
        <a href="{{ route('admin.teams.edit', $team->id) }}" class="btn btn-warning mt-3">Chỉnh sửa</a>
    </div>
@endsection
