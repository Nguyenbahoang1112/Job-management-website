<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ArrayFormat;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\DateFormat;
use Illuminate\Http\Request;
use App\Helpers\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Repository\Tag\TagRepository;
use App\Http\Repository\Task\TaskRepository;
use App\Http\Repository\User\UserRepository;
use App\Http\Repository\TaskTag\TaskTagRepository;
use App\Http\Requests\Admin\Task\CreateTaskRequest;
use App\Http\Repository\RepeatRule\RepeatRuleRepository;
use App\Http\Repository\TaskDetail\TaskDetailRepository;
use App\Models\RepeatRule;

class UserTaskController extends Controller
{
    protected $taskRepository;
    protected $taskDetailRepository;
    protected $userRepository;
    protected $tagRepository;
    protected $taskTagRepository;
    protected $repeatRuleRepository;

    public function __construct(TaskRepository $taskRepository, TaskDetailRepository $taskDetailRepository, UserRepository $userRepository, TagRepository $tagRepository, TaskTagRepository $taskTagRepository, RepeatRuleRepository $repeatRuleRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->taskDetailRepository = $taskDetailRepository;
        $this->userRepository = $userRepository;
        $this->tagRepository = $tagRepository;
        $this->taskTagRepository = $taskTagRepository;
        $this->repeatRuleRepository = $repeatRuleRepository;

        // Apply the admin middleware to all methods in this controller
        $this->middleware('admin');
    }
    public function index()
    {
        // Get all users
        $tasks = $this->taskRepository->getAllTeamTaskByAdmin('*', 10);
        return view('admin.task.index', compact('tasks'));
    }
    public function create()
    {
        $users = $this->userRepository->getAllUser()->get();
        $tags = $this->tagRepository->getAllByAdmin()->get();

        return view('admin.task.create', compact('users', 'tags'));
    }

    public function store(CreateTaskRequest $request)
    {
        switch ($request->due_date_select) {
            case RepeatRule::TODAY:
                $due_date = now()->startOfDay();
                break;
            case RepeatRule::TOMORROW:
                $due_date = now()->addDay()->startOfDay();
                break;
            case RepeatRule::WEEKEND:
                $due_date = now()->next(Carbon::SUNDAY)->startOfDay();
                break;
            default:
                $due_date = $request->due_date ;
                break;
        }
        //format deadline của task
        $due_date = Carbon::parse($due_date);
        // dd($due_date);

        //xét kiểu lặp lại
        switch ($request->repeat_type) {
            //tạo mới task không lặp lại
            case RepeatRule::REPEAT_TYPE_NONE:
                DB::transaction(function () use ($request, $due_date) {
                    //tạo task
                    $taskCreate = $this->taskRepository->createTaskToUser($request->user_id);

                    // Tạo task detail
                    $taskDetailCreate = $this->taskDetailRepository->createTaskDetail($request, $due_date, $taskCreate->id);

                    // Tạo task tag (n-n)
                    if ($request->tag_ids) {
                        $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                        $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                    }
                });
                break;
            //tạo mới task lặp lại "hàng ngày"
            case RepeatRule::REPEAT_TYPE_DAILY:
                DB::transaction(function () use ($request, $due_date) {
                    //tạo task
                    $taskCreate = $this->taskRepository->createTaskToUser($request->user_id);

                    // Tạo task tag (n-n)
                    if ($request->tag_ids) {
                        //format collection sang array
                        $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                        $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                    }

                    //tạo repeat_rule cho task lặp lại bởi admin
                    $repeatRuleCreate = $this->repeatRuleRepository->createByAdmin($request, $taskCreate->id);

                    //biến lưu stt task detail
                    $parent_id = 1;
                    //tạo mảng lưu taskDetails rỗng
                    $taskDetails = [];

                    // lặp lại hàng ngày theo số lần (repeat_interval)
                    if ($request->repeat_option === "interval")
                    {
                        //vòng lặp tạo task detail theo số lần lặp lại
                        for ($i = 0; $i <= $request->repeat_interval; $i++) {

                            //thêm task detail vào array
                            $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date->copy(), $taskCreate->id, $parent_id);

                            //thêm task detail vào mảng task details
                            array_push($taskDetails, $taskDetail);

                            //tăng parent_id
                            $parent_id++;

                            //tăng 1 ngày theo quy tắc lặp lại (hàng ngày)
                            $due_date = $due_date->addDay();
                        }
                        //insert mảng task detail vào db
                        $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                    }
                    else if ($request->repeat_option === "endDate")
                    {
                        //vòng lặp tạo task detail theo hạn lặp lại
                        while ($due_date <= Carbon::parse($request->repeat_due_date)) {
                            //thêm task detail vào array
                            $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date->copy(), $taskCreate->id, $parent_id);

                            //thêm task detail vào mảng task details
                            array_push($taskDetails, $taskDetail);

                            //tăng parent_id
                            $parent_id++;

                            //tăng ngày theo quy tắc lặp lại (hàng ngày)
                            $due_date = $due_date->addDay();
                        }
                        //insert mảng task detail vào db
                        $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                    }
                });
                break;
                //tạo mới task lặp lại "ngày trong tuần"
                case RepeatRule::REPEAT_TYPE_DAY_OF_WEEK:
                    DB::transaction(function () use ($request, $due_date) {
                        //tạo task
                        $taskCreate = $this->taskRepository->createTaskToUser($request->user_id);

                        // Tạo task tag (n-n)
                        if ($request->tag_ids) {
                            //format collection sang array
                            $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                            $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                        }

                        //tạo repeat_rule cho task lặp lại bởi admin
                        $repeatRuleCreate = $this->repeatRuleRepository->createByAdmin($request, $taskCreate->id);

                        //biến lưu stt task detail
                        $parent_id = 1;
                        //tạo mảng lưu taskDetails rỗng
                        $taskDetails = [];

                        // lặp lại ngày trong tuần theo số lần (repeat_interval)
                        if ($request->repeat_option === "interval")
                        {
                            //biến đếm số lần lặp lại
                            $indexOfInterval = 0;

                            //vòng lặp tạo task detail theo số lần lặp lại
                            while ($indexOfInterval <= $request->repeat_interval) {
                                //kiểm tra khoảng thứ 2 đến t6
                                if ($due_date->dayOfWeek >= 1 && $due_date->dayOfWeek <= 5) {
                                    //thêm task detail vào array
                                    $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date->copy(), $taskCreate->id, $parent_id);

                                    //thêm task detail vào mảng task details
                                    array_push($taskDetails, $taskDetail);

                                    //tăng parent_id
                                    $parent_id++;
                                    //tăng biến đếm số lần lặp lại
                                    $indexOfInterval++;
                                }
                                //tăng ngày theo quy tắc lặp lại (ngày trong tuần)
                                if ($due_date->dayOfWeek == 5) {
                                    //nếu đang là thứ 6 thì tăng lên 3 ngày để tới thứ 2
                                    $due_date->addDay(3);
                                } else if ($due_date->dayOfWeek == 6) {
                                    //nếu đang là thứ 7 thì tăng lên 2 ngày để tới thứ 2
                                    $due_date->addDay(2);
                                } else {
                                    //tăng 1 ngày
                                    $due_date->addDay();
                                }
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        } else if ($request->repeat_option === "endDate")
                        {
                            //vòng lặp tạo task detail theo hạn lặp lại
                            while ($due_date <= Carbon::parse($request->repeat_due_date)) {
                                //kiểm tra khoảng thứ 2 đến t6
                                if ( $due_date->dayOfWeek >= 1 && $due_date->dayOfWeek <= 5) {
                                    //thêm task detail vào array
                                    $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date->copy(), $taskCreate->id, $parent_id);

                                    //thêm task detail vào mảng task details
                                    array_push($taskDetails, $taskDetail);

                                    //lấy taskDetail->id vừa tạo
                                    $parent_id++;
                                }
                                //tăng ngày theo quy tắc lặp lại (ngày trong tuần)
                                if ($due_date->dayOfWeek == 5) {
                                    //nếu đang là thứ 6 thì tăng lên 3 ngày để tới thứ 2
                                    $due_date->addDay(3);
                                } else if ($due_date->dayOfWeek == 6) {
                                    //nếu đang là thứ 7 thì tăng lên 2 ngày để tới thứ 2
                                    $due_date->addDay(2);
                                } else {
                                    //tăng 1 ngày
                                    $due_date->addDay();
                                }
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        }
                    });
                break;
            //tạo mới task lặp lại "hàng tháng"
            case RepeatRule::REPEAT_TYPE_MONTHLY:
                DB::transaction(function () use ($request, $due_date) {
                    //tạo task
                    $taskCreate = $this->taskRepository->createTaskToUser($request->user_id);

                    // Tạo task tag (n-n)
                    if ($request->tag_ids) {
                        //format collection sang array
                        $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                        $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                    }

                    //tạo repeat_rule cho task lặp lại bởi admin
                    $repeatRuleCreate = $this->repeatRuleRepository->createByAdmin($request, $taskCreate->id);

                    //biến lưu stt task detail
                    $parent_id = 1;
                    //tạo mảng lưu taskDetails rỗng
                    $taskDetails = [];

                    // lặp lại hàng tháng theo số lần (repeat_interval)
                    if ($request->repeat_option === "interval")
                    {
                        //biến đếm số lần lặp lại
                        $indexOfInterval = 0;

                        //vòng lặp tạo task detail theo số lần lặp lại
                        while ($indexOfInterval <= $request->repeat_interval) {
                            //tạo taskDetail
                            $taskDetail = $this->taskDetailRepository->createTaskDetail($request, $due_date->copy(), $taskCreate->id, $parent_id);

                            //tăng parent_id
                            $parent_id++;

                            //tăng biến đếm số lần lặp lại
                            $indexOfInterval++;

                            //tăng 1 tháng
                            $due_date->addMonth();
                        }
                        //insert mảng task detail vào db
                        $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                    } else if ($request->repeat_option === "endDate")
                    {
                        //vòng lặp tạo task detail theo hạn lặp lại
                        while ($due_date <= Carbon::parse($request->repeat_due_date)) {

                            //tạo taskDetail
                            $taskDetail = $this->taskDetailRepository->createTaskDetail($request, $due_date->copy(), $taskCreate->id, $parent_id);

                            //lấy taskDetail->id vừa tạo
                            $parent_id = $taskDetail->id;

                            //tăng 1 tháng
                            $due_date->addMonth();
                        }
                    }
                });
            break;
            default:
                break;
        }

        return RedirectResponse::redirectWithMessage('admin.tasks.index', null, RedirectResponse::SUCCESS, 'Tạo task thành công!');
    }

    public function show($id)
    {
        $taskDetails = $this->taskDetailRepository->getAllByTaskId($id);
        // DateFormat::formatDate($taskDetails->due_date);
        return view('admin.task.show', compact('taskDetails'));
    }

    public function edit($id)
    {
        $taskDetail = $this->taskDetailRepository->find($id);
        return view('admin.task.update', compact('taskDetail'));
    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {
        $taskRemove = $this->taskRepository->delete($id);
        if ($taskRemove)
        {
            return RedirectResponse::redirectWithMessage('admin.tasks.index', null, RedirectResponse::SUCCESS, 'Xóa task thành công!');
        }
        return RedirectResponse::redirectWithMessage('admin.tasks.index', null, RedirectResponse::ERROR, 'Không xóa được task!');
    }

}
