<div class="container-fluid">
    <h1>{{ __('messages.create_task_group') }}</h1>

    <!-- Thông báo lỗi -->
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
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

    <!-- Form tạo task_group -->
    <div class="card">
        <div class="card-header">
            {{ __('messages.create_task_group_form') }}
        </div>
        <div class="card-body">
            <form action="{{ route('task_groups.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">{{ __('messages.name') }}</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">{{ __('messages.create') }}</button>
                    <a href="{{ route('task_groups.index') }}" class="btn btn-secondary">{{ __('messages.back') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>