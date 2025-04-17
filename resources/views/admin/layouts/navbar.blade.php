{{-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav> --}}


<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm px-4 py-2 rounded-bottom">
    <div class="container-fluid">
        <div class="navbar__user ms-auto dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                id="userDropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4 me-2"></i>
                <span>{{ Auth::user()->name ?? 'Admin' }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3" aria-labelledby="userDropdown">
                <li>
                    <a class="dropdown-item" href="">
                        <i class="bi bi-gear me-2"></i>Profile
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="{{ route('admin.auth.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Hiệu ứng hover cho dropdown */
    .dropdown:hover .dropdown-menu {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    /* Ẩn dropdown mặc định */
    .dropdown-menu {
        display: none;
        opacity: 0;
        position: absolute;
        right: 0;
        transform: translateY(-10px);
        /* Tạo hiệu ứng trượt xuống */
        transition: all 0.3s ease-out;
    }

    /* Tạo hiệu ứng hover cho các mục trong dropdown */
    .dropdown-item {
        transition: background-color 0.2s, color 0.2s;
        /* Thêm hiệu ứng khi hover */
    }

    .dropdown-item:hover {
        background-color: #007bff;
        /* Màu nền khi hover */
        color: white;
        /* Màu chữ khi hover */
        border-radius: 5px;
    }

    /* Tinh chỉnh đường viền và bóng cho menu */
    .dropdown-menu {
        border: 1px solid #ddd;
        /* Thêm đường viền nhẹ */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* Tạo bóng nhẹ */
    }
</style>
