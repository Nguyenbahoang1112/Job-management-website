@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa thẻ')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chỉnh sửa thẻ</h2>

        <form action="{{ route('admin.tags.update', $tag->id) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- Email --}}
            <div class="mb-3">
                <label for="name" class="form-label">Tên:</label>
                <input type="name" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $tag->name) }}" required>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

         
            <div class="mb-3">
                <label for="user_id" class="form-label">Người sở hữu:</label>
                <select name="user_id" class="form-select select2">
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $tag->user_id ?? '') == $user->id ? 'selected' : '' }}>
                            {{ $user->email }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
            
        
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>


@endsection
  
@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Chọn người sở hữu...",
            allowClear: true
        });
    });
</script>
@endsection
