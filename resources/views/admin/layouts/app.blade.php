<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sidebar {
            height: 100vh;
            position: fixed;
            left: 0;
            top: 56px;
            width: 220px;
            background-color: #f8f9fa;
            padding-top: 1rem;
            border-right: 1px solid #dee2e6;
        }

        .main-content {
            margin-left: 220px;
            margin-top: 56px;
            padding: 1.5rem;
            flex: 1;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    @include('Admin.layouts.navbar')

    <!-- Sidebar -->
    <div class="sidebar">
        @include('Admin.layouts.sidebar')
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
