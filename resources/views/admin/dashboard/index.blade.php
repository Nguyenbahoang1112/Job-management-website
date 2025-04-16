@extends('Admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Task Management Dashboard</h2>

        {{-- THỐNG KÊ --}}
        <div class="row text-white">
            <div class="col-md-3">
                <div class="card bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Tasks</h5>
                        <p class="card-text fs-4">120</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Completed</h5>
                        <p class="card-text fs-4">80</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark mb-3">
                    <div class="card-body">
                        <h5 class="card-title">In Progress</h5>
                        <p class="card-text fs-4">30</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Overdue</h5>
                        <p class="card-text fs-4">10</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- BIỂU ĐỒ --}}
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">User Time Usage This Week</h5>
                <canvas id="timeChart" height="120"></canvas>
            </div>
        </div>

        {{-- TASK GẦN ĐÂY --}}
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Recent Tasks</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Design UI</td>
                            <td>Huy</td>
                            <td>Completed</td>
                            <td>10/04/2025</td>
                        </tr>
                        <tr>
                            <td>Fix bug login</td>
                            <td>Lan</td>
                            <td>In Progress</td>
                            <td>18/04/2025</td>
                        </tr>
                        <tr>
                            <td>Viết tài liệu</td>
                            <td>Minh</td>
                            <td>Overdue</td>
                            <td>05/04/2025</td>
                        </tr>
                        <tr>
                            <td>Tạo biểu mẫu</td>
                            <td>Thảo</td>
                            <td>Completed</td>
                            <td>09/04/2025</td>
                        </tr>
                        <tr>
                            <td>Thống kê dữ liệu</td>
                            <td>Huy</td>
                            <td>In Progress</td>
                            <td>20/04/2025</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('timeChart').getContext('2d');
        const timeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Huy', 'Lan', 'Minh', 'Thảo'],
                datasets: [{
                    label: 'Hours Spent',
                    data: [12, 8, 10, 5],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                }
            }
        });
    </script>
@endpush
