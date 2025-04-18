@extends('Admin.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Quản lý đội nhóm</h2>

        <!-- Tìm kiếm -->
        <form action="{{ route('admin.teams.index') }}" method="GET" class="d-flex align-items-center"
            style=" margin-bottom: 0;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm kiếm người dùng"
                value="{{ request()->get('search') }}" style="max-width: 200px; height: 40px;">
            <button type="submit" class="btn btn-secondary btn-sm ms-2" style="height: 40px; width: 100px;">Tìm
                kiếm</button>
        </form>

        <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">+ Thêm đội nhóm mới</a>
    </div>


    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th class="text-center" style="width: 50px;">STT</th>
                <th class="text-center">Tên</th>
                <th class="text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($teams as $team)
                <tr>
                    {{-- <td class="text-center">{{ $team->id }}</td> --}}
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">
                      {{ $team->name }}
                    </td>
                    <td class="d-flex justify-content-center align-items-center gap-2">
                        <a href="{{ route('admin.teams.show', $team->id) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> Xem
                        </a>
                        <a href="{{ route('admin.teams.edit', $team->id) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil-square"></i> Sửa
                        </a>
                        <!-- Delete -->


                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $team->id }}">
                            <i class="bi bi-trash"></i> Xóa
                        </button>

                        <div class="modal fade" id="deleteModal{{ $team->id }}" tabindex="-1" aria-labelledby="exampleModalLabel{{ $team->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel{{ $team->id }}">Cảnh báo</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Bạn có muốn xóa thẻ <strong>{{ $team->name }}</strong> không ?
                                    </div>
                                    <div class="modal-footer">
                                        <form id="deleteForm{{ $team->id }}" action="{{ route('admin.teams.destroy', $team->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Có</button>
                                        </form>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Không</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-block card-footer">
        {{ $teams->links('pagination::bootstrap-5') }}
    </div>
    
    
  
    
@endsection
