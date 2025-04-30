<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\RepeatRule;
use App\Helpers\ArrayFormat;
use Illuminate\Http\Request;
use App\Helpers\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Redirect;
use App\Http\Repository\Tag\TagRepository;
use App\Http\Repository\Task\TaskRepository;
use App\Http\Repository\Team\TeamRepository;
use App\Http\Repository\User\UserRepository;
use App\Http\Repository\TaskTag\TaskTagRepository;
use App\Http\Repository\RepeatRule\RepeatRuleRepository;
use App\Http\Repository\TaskDetail\TaskDetailRepository;
use App\Http\Requests\Admin\TeamRequest\TeamStoreRequest;
use App\Http\Requests\Admin\TeamRequest\TeamUpdateRequest;
use App\Http\Requests\Admin\TeamRequest\AddUserToTeamRequest;

class TeamController extends Controller
{
    protected $teamRepository;
    protected $userRepository;
    protected $tagRepository;
    protected $taskRepository;
    protected $taskDetailRepository;
    protected $taskTagRepository;
    protected $repeatRuleRepository;

    public function __construct(TeamRepository $teamRepository,UserRepository $userRepository,
    TagRepository $tagRepository, TaskRepository $taskRepository, TaskDetailRepository $taskDetailRepository,
    TaskTagRepository $taskTagRepository, RepeatRuleRepository $repeatRuleRepository)
    {
        $this->middleware('admin');
        $this->tagRepository = $tagRepository;
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
        $this->taskRepository = $taskRepository;
        $this->taskDetailRepository = $taskDetailRepository;
        $this->taskTagRepository = $taskTagRepository;
        $this->repeatRuleRepository = $repeatRuleRepository;
    }

    public function index()
    {
        $teams = $this->teamRepository->getAll();
        return view('admin.team.index', ['teams' => $teams]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.team.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeamStoreRequest $request)
    {
        try {
            $this->teamRepository->create([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return RedirectResponse::redirectWithMessage('admin.teams.index', [], RedirectResponse::SUCCESS, 'Tạo đội nhóm thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.teams.create', [], RedirectResponse::ERROR, 'Tạo đội nhóm thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $users = $this->userRepository->getAllUser()->get();
        $teams = $this->teamRepository->getAll();
        $team = $this->teamRepository->find($id);
        $tasks = $this->taskRepository->getAllTeamTaskByAdmin('*',$id);
        // dd($tasks);
        if(!$team){
            return RedirectResponse::redirectWithMessage('admin.teams.index',RedirectResponse::ERROR,'Không tìm thấy đội nhóm');
        }
        return view('admin.team.show',['team'=>$team,'teams'=>$teams,'users'=>$users, 'tasks' => $tasks]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try{
            $team = $this->teamRepository->find($id);
            if(!$team){
                return RedirectResponse::redirectWithMessage('admin.teams.index',[],RedirectResponse::WARNING,'Đội nhóm không tồn tại!');
            }
            return view('admin.team.update',['team' => $team]);
        }
        catch(\Exception $e){
            return RedirectResponse::redirectWithMessage('admin.teams.index',[],RedirectResponse::ERROR,'Có lỗi xảy ra'.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeamUpdateRequest $request, string $id)
    {
        try{
            $this->teamRepository->update([
                'name' => $request->name,
                'description' => $request->description
            ],$id);
            return RedirectResponse::redirectWithMessage('admin.teams.index',[],RedirectResponse::SUCCESS,'Cập nhật thành công');

        }
        catch(\Exception $e){
            return RedirectResponse::redirectWithMessage('admin.teams.edit',[],RedirectResponse::ERROR,'Cập nhật thất bại'.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->teamRepository->delete($id);
            return RedirectResponse::redirectWithMessage('admin.teams.index',[],RedirectResponse::SUCCESS, 'Xóa đội nhóm thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.teams.index',[],RedirectResponse::ERROR, 'Xóa đội nhóm thất bại: ' . $e->getMessage());
        }
    }

    public function addUsersToTeam(AddUserToTeamRequest $request)
    {
        try {
            $team = $this->teamRepository->find($request->team_id);
            $team->users()->syncWithoutDetaching($request->user_ids);
            return RedirectResponse::redirectWithMessage('admin.teams.show', [$team->id],RedirectResponse::SUCCESS,'Thêm người dùng vào nhóm thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.teams.index',[],RedirectResponse::ERROR, 'Lỗi khi thêm người dùng vào nhóm: ' . $e->getMessage());
        }
    }

    public function removeUser($teamId, $userId)
    {
        try {
            $team = $this->teamRepository->find($teamId);
            $team->users()->detach($userId);

            return RedirectResponse::redirectWithMessage('admin.teams.show', [$team->id],RedirectResponse::SUCCESS, 'Xóa người dùng khỏi nhóm thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.teams.show', [], RedirectResponse::ERROR,'Lỗi khi xóa người dùng khỏi nhóm: ' . $e->getMessage());
        }
    }

    public function addTaskView($id)
    {
        $team = $this->teamRepository->find($id);
        $tags = $this->tagRepository->getAllByAdmin()->get();

        return view('admin.team.add_task_to_team', compact('team', 'tags'));
    }
    public function addTaskToTeam(Request $request, $teamId)
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
        //vòng lặp người dùng trong team
        foreach ($request->input('users') as $userId) {
            $due_date_copy = $due_date->copy();
            //xét kiểu lặp lại
            switch ($request->repeat_type) {
                //tạo mới task không lặp lại
                case RepeatRule::REPEAT_TYPE_NONE:
                    DB::transaction(function () use ($request, $due_date_copy, $teamId, $userId) {
                        //tạo task
                        $taskCreate = $this->taskRepository->create([
                            'user_id' => $userId,
                            'team_id' => $teamId,
                            'is_admin_created' => Task::TASK_CREATED_BY_ADMIN,
                        ]);

                        // Tạo task detail
                        $taskDetailCreate = $this->taskDetailRepository->createTaskDetail($request, $due_date_copy, $taskCreate->id);

                        // Tạo task tag (n-n)
                        if ($request->tag_ids) {
                            $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                            $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                        }
                    });
                    break;
                //tạo mới task lặp lại "hàng ngày"
                case RepeatRule::REPEAT_TYPE_DAILY:
                    DB::transaction(function () use ($request, $due_date_copy, $teamId, $userId) {
                        //tạo task
                        $taskCreate = $this->taskRepository->create([
                            'user_id' => $userId,
                            'team_id' => $teamId,
                            'is_admin_created' => Task::TASK_CREATED_BY_ADMIN,
                        ]);

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
                                $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id);

                                //thêm task detail vào mảng task details
                                array_push($taskDetails, $taskDetail);

                                //tăng parent_id
                                $parent_id++;

                                //tăng 1 ngày theo quy tắc lặp lại (hàng ngày)
                                $due_date_copy = $due_date_copy->addDay();
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        }
                        else if ($request->repeat_option === "endDate")
                        {
                            //vòng lặp tạo task detail theo hạn lặp lại
                            while ($due_date_copy <= Carbon::parse($request->repeat_due_date)) {

                                //thêm task detail vào array
                                $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id);

                                //thêm task detail vào mảng task details
                                array_push($taskDetails, $taskDetail);

                                //tăng parent_id
                                $parent_id++;

                                //tăng ngày theo quy tắc lặp lại (hàng ngày)
                                $due_date = $due_date_copy->addDay();
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        }
                    });
                    break;
                    //tạo mới task lặp lại "ngày trong tuần"
                    case RepeatRule::REPEAT_TYPE_DAY_OF_WEEK:
                        DB::transaction(function () use ($request, $due_date_copy, $teamId, $userId) {
                            //tạo task
                            $taskCreate = $this->taskRepository->create([
                                'user_id' => $userId,
                                'team_id' => $teamId,
                                'is_admin_created' => Task::TASK_CREATED_BY_ADMIN,
                            ]);

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
                                    if ($due_date_copy->dayOfWeek >= 1 && $due_date_copy->dayOfWeek <= 5) {
                                        //thêm task detail vào array
                                        $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id);

                                        //thêm task detail vào mảng task details
                                        array_push($taskDetails, $taskDetail);

                                        //tăng parent_id
                                        $parent_id++;
                                        //tăng biến đếm số lần lặp lại
                                        $indexOfInterval++;
                                    }
                                    //tăng ngày theo quy tắc lặp lại (ngày trong tuần)
                                    if ($due_date_copy->dayOfWeek == 5) {
                                        //nếu đang là thứ 6 thì tăng lên 3 ngày để tới thứ 2
                                        $due_date_copy->addDay(3);
                                    } else if ($due_date_copy->dayOfWeek == 6) {
                                        //nếu đang là thứ 7 thì tăng lên 2 ngày để tới thứ 2
                                        $due_date_copy->addDay(2);
                                    } else {
                                        //tăng 1 ngày
                                        $due_date_copy->addDay();
                                    }
                                }
                                //insert mảng task detail vào db
                                $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                            } else if ($request->repeat_option === "endDate")
                            {
                                //vòng lặp tạo task detail theo hạn lặp lại
                                while ($due_date_copy <= Carbon::parse($request->repeat_due_date)) {
                                    //kiểm tra khoảng thứ 2 đến t6
                                    if ( $due_date_copy->dayOfWeek >= 1 && $due_date_copy->dayOfWeek <= 5) {
                                        //thêm task detail vào array
                                        $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id);

                                        //thêm task detail vào mảng task details
                                        array_push($taskDetails, $taskDetail);

                                        //lấy taskDetail->id vừa tạo
                                        $parent_id++;
                                    }
                                    //tăng ngày theo quy tắc lặp lại (ngày trong tuần)
                                    if ($due_date_copy->dayOfWeek == 5) {
                                        //nếu đang là thứ 6 thì tăng lên 3 ngày để tới thứ 2
                                        $due_date_copy->addDay(3);
                                    } else if ($due_date_copy->dayOfWeek == 6) {
                                        //nếu đang là thứ 7 thì tăng lên 2 ngày để tới thứ 2
                                        $due_date_copy->addDay(2);
                                    } else {
                                        //tăng 1 ngày
                                        $due_date_copy->addDay();
                                    }
                                }
                                //insert mảng task detail vào db
                                $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                            }
                        });
                    break;
                //tạo mới task lặp lại "hàng tháng"
                case RepeatRule::REPEAT_TYPE_MONTHLY:
                    DB::transaction(function () use ($request, $due_date_copy, $teamId, $userId) {
                        //tạo task
                        $taskCreate = $this->taskRepository->create([
                            'user_id' => $userId,
                            'team_id' => $teamId,
                            'is_admin_created' => Task::TASK_CREATED_BY_ADMIN,
                        ]);

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
                                $taskDetail = $this->taskDetailRepository->createTaskDetail($request, $due_date_copy->copy(), $taskCreate->id, $parent_id);

                                //tăng parent_id
                                $parent_id++;

                                //tăng biến đếm số lần lặp lại
                                $indexOfInterval++;

                                //tăng 1 tháng
                                $due_date_copy->addMonth();
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        } else if ($request->repeat_option === "endDate")
                        {
                            //vòng lặp tạo task detail theo hạn lặp lại
                            while ($due_date_copy <= Carbon::parse($request->repeat_due_date)) {

                                //tạo taskDetail
                                $taskDetail = $this->taskDetailRepository->createTaskDetail($request, $due_date_copy->copy(), $taskCreate->id, $parent_id);

                                //lấy taskDetail->id vừa tạo
                                $parent_id = $taskDetail->id;

                                //tăng 1 tháng
                                $due_date_copy->addMonth();
                            }
                        }
                    });
                break;
                default:
                    break;
            }
        }
        return RedirectResponse::redirectWithMessage('admin.teams.show', [$teamId],RedirectResponse::SUCCESS,'Thêm task thành công!');
    }

}
