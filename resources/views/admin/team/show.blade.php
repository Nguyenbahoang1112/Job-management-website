@extends('admin.layouts.app')

@section('title', 'Chi tiết nhóm')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Chi tiết nhóm</h2>

        {{-- Thông tin nhóm --}}
        <div class="card mb-4">
            <div class="card-body">
                <p><strong>ID:</strong> {{ $team->id }}</p>
                <p><strong>Tên nhóm:</strong> {{ $team->name }}</p>
                <p><strong>Mô tả:</strong> {{ $team->description ?? 'Không có mô tả' }}</p>
                <p><strong>Số thành viên:</strong> {{ $team->users->count() }}</p>
                <p><strong>Ngày tạo:</strong> {{ $team->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Cập nhật gần nhất:</strong> {{ $team->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Form thêm người dùng --}}
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Thêm người dùng vào nhóm</h5>
                <form action="{{ route('admin.teams.addUsersToTeam') }}" method="POST">
                    @csrf
                    <input type="hidden" name="team_id" value="{{ $team->id }}">

                    <div class="form-group mb-3">
                        <label for="user_ids">Chọn người dùng:</label>
                        <select name="user_ids[]" id="user_ids" class="form-control" multiple required>
                            @foreach ($users as $user)
                                @if (!$team->users->contains($user->id))
                                    <option value="{{ $user->id }}">{{ $user->email }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('user_ids')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Thêm vào nhóm</button>
                </form>
            </div>
        </div>

        {{-- Danh sách thành viên --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Danh sách thành viên</h5>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Ngày tham gia</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($team->users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1"
                                        aria-labelledby="exampleModalLabel{{ $user->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel{{ $user->id }}">Cảnh báo</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Bạn có muốn xóa thẻ <strong>{{ $user->email }}</strong> không ?
                                                </div>
                                                <div class="modal-footer">
                                                    <form id="deleteForm{{ $user->id }}"
                                                        action="{{route('admin.teams.removeUser', [$team->id, $user->id]) }}}}" method="POST"
                                                        style="display: inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Có</button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Không</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal{{ $user->id }}">
                                    <i class="bi bi-trash"></i> Xóa
                                    </button>
                                  
                                </td>
                            </tr>
                        @endforeach

                        @if ($team->users->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center">Chưa có thành viên nào.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary mt-3">Quay lại danh sách</a>
        <a href="{{ route('admin.teams.edit', $team->id) }}" class="btn btn-warning mt-3">Chỉnh sửa</a>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Choices('#user_ids', {
                removeItemButton: true,
                placeholderValue: 'Chọn người dùng',
                searchPlaceholderValue: 'Tìm kiếm...',
                shouldSort: false,
            });
        });
    </script>
@endsection
