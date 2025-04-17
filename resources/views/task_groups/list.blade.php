<div class="container-fluid">
    <h1>{{ __('messages.task_groups_title') }}</h1>

    <!-- Thông báo thành công -->
    @if (session('success') || isset($success))
        <div class="alert alert-success">
            {{ session('success', $success ?? '') }}
        </div>
    @endif

    <!-- Thông báo lỗi -->
    @if (session('error') || isset($error))
        <div class="alert alert-danger">
            {{ session('error', $error ?? '') }}
        </div>
    @endif

    <!-- Lỗi validation -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Danh sách task_groups -->
    <div class="card">
        <div class="card-header">
            {{ __('messages.task_groups_list') }}
        </div>
        <div class="card-body">
            @if ($taskGroups->isNotEmpty())
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.id') }}</th>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($taskGroups as $taskGroup)
                            <tr>
                                <td>{{ $taskGroup->id }}</td>
                                <td>{{ $taskGroup->name }}</td>
                                <td>
                                    <a href="{{ route('task_groups.edit', $taskGroup->id) }}" class="btn btn-primary btn-sm">{{ __('messages.edit') }}</a>
                                    <form action="{{ route('task_groups.destroy', $taskGroup->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">{{ __('messages.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Phân trang -->
                @if ($taskGroups instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-3">
                        {{ $taskGroups->links() }}
                    </div>
                @endif
            @else
                <p>{{ __('messages.no_task_groups') }}</p>
            @endif
        </div>
    </div>