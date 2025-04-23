@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa thẻ')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chỉnh sửa đội nhóm</h2>

        <form action="{{ route('admin.teams.update', $team->id) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Email --}}
            <div class="mb-3">
                <label for="name" class="form-label">Tên:</label>
                <input type="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $team->name) }}" required>
                @error(' <label for="name" class="form-label">Tên:</label>name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <label for="name" class="form-label">Mô tả:</label>
                <input type="name" name="description" class="form-control @error('description') is-invalid @enderror"
                    value="{{ old('description', $team->description) }}" required>
                @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>


@endsection
  
