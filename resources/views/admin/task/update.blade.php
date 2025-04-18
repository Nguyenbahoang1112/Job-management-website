@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa người dùng')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chỉnh sửa người dùng</h2>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Trạng thái --}}
            <div class="mb-3">
                <label for="status" class="form-label">Trạng thái:</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="1" {{ $user->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $user->status == 0 ? 'selected' : '' }}>Banned</option>
                </select>
                @error('status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            {{-- Password --}}
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
        <form action="{{ route('admin.users.change-pass', $user->id) }}" method="POST" class="mt-3">
            @csrf
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu:</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    placeholder="Nhập mật khẩu mới">
                <input type="password" name="password_confirmation" class="form-control mt-2"
                    placeholder="Nhập lại mật khẩu">
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
            </div>
        @endsection
        <!-- Toast Container -->
