<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Repository\Team\TeamRepository;
use App\Http\Repository\User\UserRepository;
use App\Http\Requests\Admin\TeamRequest\TeamStoreRequest;
use App\Http\Requests\Admin\TeamRequest\TeamUpdateRequest;
use App\Helpers\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use App\Http\Requests\Admin\TeamRequest\AddUserToTeamRequest;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $teamRepository;
    protected $userRepository;

    public function __construct(TeamRepository $teamRepository,UserRepository $userRepository){
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
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
        $users = $this->userRepository->getAllUser();
        $teams = $this->teamRepository->getAll();
        $team = $this->teamRepository->find($id);
        if(!$team){
            return RedirectResponse::redirectWithMessage('admin.teams.index',RedirectResponse::ERROR,'Không tìm thấy đội nhóm');
        }
        return view('admin.team.show',['team'=>$team,'teams'=>$teams,'users'=>$users]);
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
}
