@extends('Admin.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Quản lý nhóm công việc</h2>

        <!-- Tìm kiếm -->
        <form action="{{ route('admin.task-groups.index') }}" method="GET" class="d-flex align-items-center"
            style=" margin-bottom: 0;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm kiếm người dùng"
                value="{{ request()->get('search') }}" style="max-width: 200px; height: 40px;">
            <button type="submit" class="btn btn-secondary btn-sm ms-2" style="height: 40px; width: 100px;">Tìm
                kiếm</button>
        </form>

        <a href="{{ route('admin.task-groups.create') }}" class="btn btn-primary">+ Thêm nhóm công việc mới</a>
    </div>




    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th class="text-center" style="width: 50px;">STT</th>
                <th class="text-center">Tên</th>
                <th class="text-center">Người tạo</th>
                <th class="text-center">Vai trò</th>
                <th class="text-center">Ngày tạo</th>
                <th class="text-center">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($taskGroups as $taskGroup)
                <tr>
                    {{-- <td class="text-center">{{ $taskGroup->id }}</td> --}}
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">
                      {{ $taskGroup->name }}
                    </td>
                    <td class = "text-center">
                        {{ $taskGroup->user->email }}
                    </td>
                    <td class="text-center">
                        @if ($taskGroup->is_admin_created)
                            <span class="badge bg-danger">Admin</span>
                        @else
                            <span class="badge bg-primary">User</span>
                        @endif
                    </td>

                    <td class = "text-center">
                        {{ $taskGroup->created_at }}
                    </td>

                    <td class="d-flex justify-content-center align-items-center gap-2">
                        <a href="{{ route('admin.task-groups.show', $taskGroup->id) }}" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> Xem
                        </a>
                        <a href="{{ route('admin.task-groups.edit', $taskGroup->id) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil-square"></i> Sửa
                        </a>
                        <!-- Delete -->


                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $taskGroup->id }}">
                            <i class="bi bi-trash"></i> Xóa
                        </button>

                        <div class="modal fade" id="deleteModal{{ $taskGroup->id }}" tabindex="-1" aria-labelledby="exampleModalLabel{{ $taskGroup->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel{{ $taskGroup->id }}">Cảnh báo</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Bạn có muốn xóa nhóm công việc <strong>{{ $taskGroup->name }}</strong> không ?
                                    </div>
                                    <div class="modal-footer">
                                        <form id="deleteForm{{ $taskGroup->id }}" action="{{ route('admin.task-groups.destroy', $taskGroup->id) }}" method="POST" style="display: inline-block;">
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
        {{ $taskGroups->links('pagination::bootstrap-5') }}
    </div>
    
    
  
    
@endsection
