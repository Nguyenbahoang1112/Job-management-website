@extends('admin.layouts.app')

@section('content')
    <style>
        .card {
            max-width: 800px;
            margin: 40px auto;
            border-radius: 15px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            width: 500px;
        }

        .repeat-options {
            display: none;
        }

        #custom-date {
            display: none;
        }
    </style>
    <div class="container mt-4">
        <h2 class="mb-4">Thêm nhiệm vụ</h2>

        <div class="card shadow p-4">
            <form action="{{ route('admin.tasks.store') }}" method="POST">
                @csrf
                <label class="form-label">Chọn người dùng</label>
                <select class="form-select mb-3" name="user_id"> {{-- user_id --}}
                    <option value="" disabled selected hidden>Chọn người dùng</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->email }}</option>
                    @endforeach
                </select>

                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <input class="form-control" type="text" name="title" placeholder="tiêu đề">
                        {{-- title --}}
                        <input class="form-control mt-2" type="text" name="description" placeholder="mô tả">
                        {{-- description --}}
                    </div>
                </div>

                <div class="mt-3">
                    <label for="time-select" class="form-label">Chọn thời gian:</label>
                    <div class="d-flex gap-2 mb-3">

                        <body class="p-4">
                            <div class="container">
                                <div class="mb-3">
                                    <select id="date-select" class="form-select" name="due_date_select">
                                        {{-- due_date_select --}}
                                        <option value="1">Hôm nay</option>
                                        <option value="2">Ngày mai</option>
                                        <option value="3">Tuần này</option>
                                        <option id="time-select" value="custom">Tùy chỉnh &gt;</option>
                                    </select>
                                </div>

                                <div id="custom-date" class="mb-3">
                                    <input type="date" id="deadline-date" name="due_date" class="form-control"
                                        value="{{ DateFormat::formatDate(now()) }}">
                                </div>
                            </div>
                    </div>
                    <input type="time" name="time" class="form-control" value="09:00">

                    <div>
                        <label class="form-label">Lặp lại</label>
                        <select class="form-select mb-3" id="repeatSelect" name="repeat_type">
                            <option selected value="0">Không lặp lại</option>
                            <option value="1">Hằng ngày</option>
                            <option value="2">Ngày trong tuần</option>
                            <option value="3">Hằng tháng</option>
                        </select>

                        <div class="repeat-options border rounded p-3">
                            <label class="form-label">Kết thúc</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="repeat_option" id="endAfter"
                                    value="interval" checked>
                                <label class="form-check-label" for="endAfter">
                                    Số lần lặp lại
                                </label>
                                <input type="number" class="form-control mt-2" value="1" min="1"
                                    name="repeat_interval"> {{-- repeat_interval --}}
                            </div>

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="repeat_option" id="endByDate"
                                    value="endDate">
                                <label class="form-check-label" for="endByDate">
                                    Vào ngày
                                </label>
                                <input type="date" class="form-control mt-2" name="repeat_due_date"
                                    value="{{ DateFormat::formatDate(now(), 'Y-m-d') }}"> {{-- repeat_interval --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="tag_ids">Chọn thẻ:</label>
                    <select name="tag_ids[]" id="tag_ids" multiple> {{-- tag --}}
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-warning">Tạo mới</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const repeatSelect = document.getElementById("repeatSelect");
        const repeatOptions = document.querySelector(".repeat-options");

        repeatSelect.addEventListener("change", function() {
            repeatOptions.style.display = this.value !== "Không lặp lại" ? "block" : "none";
        });
    </script>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userSelect = document.getElementById('tag_ids');
            const choices = new Choices(userSelect, {
                removeItemButton: true,
                placeholderValue: 'Chọn thẻ',
                searchPlaceholderValue: 'Tìm kiếm...',
                maxItemCount: -1,
                shouldSort: true
            });
        });
    </script>
    <script>
        const timeSelect = document.getElementById('date-select');
        const customDate = document.getElementById('custom-date');

        timeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDate.style.display = 'block';
            } else {
                customDate.style.display = 'none';
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const radios = document.querySelectorAll('input[name="repeat_option"]');
            const repeatInputs = document.querySelectorAll('.repeat-input');

            function toggleRepeatInputs() {
                const selectedValue = document.querySelector('input[name="repeat_option"]:checked').value;
                repeatInputs.forEach(input => input.style.display = 'none');

                if (selectedValue === 'interval') {
                    document.querySelector('.repeat-interval').style.display = 'block';
                } else if (selectedValue === 'date') {
                    document.querySelector('.repeat-date').style.display = 'block';
                }
            }

            radios.forEach(radio => {
                radio.addEventListener('change', toggleRepeatInputs);
            });

            toggleRepeatInputs(); // Khởi động lần đầu
        });
    </script>
@endsection
