@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <h2>Thêm người dùng vào nhóm</h2>

        <form action="{{ route('admin.teams.addUsersToTeam') }}" method="POST">
            @csrf

            <div class="form-group mb-3">
                <label for="team_id">Chọn nhóm:</label>
                <select name="team_id" class="form-control">
                    <option value="">-- Chọn nhóm --</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="user_ids">Chọn người dùng:</label>
                <select name="user_ids[]" id="user_ids" multiple>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Thêm vào nhóm</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userSelect = document.getElementById('user_ids');
            const choices = new Choices(userSelect, {
                removeItemButton: true,
                placeholderValue: 'Chọn người dùng',
                searchPlaceholderValue: 'Tìm kiếm...',
                maxItemCount: -1,
                shouldSort: false
            });
        });
    </script>
@endsection
