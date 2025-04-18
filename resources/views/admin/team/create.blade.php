@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <h2>Tạo thẻ mới</h2>

        <!-- Display Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.teams.store') }}" method="POST">
            @csrf
            {{-- Email --}}
            <div class="mb-3">
                <label for="name" class="form-label">Tên:</label>
                <input type="name" name="name" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Thêm thẻ</button>
            <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection

