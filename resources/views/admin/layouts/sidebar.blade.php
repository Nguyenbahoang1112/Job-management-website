{{-- <ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link active" href="#"><i class="bi bi-house-door"></i> Dashboard</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-people"></i> Users</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-box"></i> Products</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-gear"></i> Settings</a>
    </li>
</ul> --}}


<aside class="sidebar">
    <ul>
        <li><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
        <li><a href="{{ route('admin.users.index') }}">Người dùng</a></li>
        <li><a href="{{ route('admin.tags.index') }}">Thẻ</a></li>
        <li><a href="{{ route('admin.task-groups.index') }}">Nhóm công việc</a></li>
        <li><a href="#">Tasks</a></li>
        <li><a href="#">Logout</a></li>
    </ul>
</aside>
