<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Repository\Team\TeamRepository;
use App\Helpers\ApiResponse;
class TeamController extends Controller
{
    protected $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->middleware('auth:sanctum');
        $this->teamRepository = $teamRepository;
    }

    public function getUserTeams(Request $request)
    {
        $userId = $request->user()->id;
        $teams = $this->teamRepository->getUserTeams($userId);

        if (empty($teams)) {
            return ApiResponse::error('No teams found for the user.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($teams, 'Teams retrieved successfully.', ApiResponse::SUCCESS);
    }
}
