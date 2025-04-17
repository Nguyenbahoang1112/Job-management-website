<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\User\TaskGroup\TaskGroupResource;
use App\Http\Repository\Admin\TaskGroupRepository;
use Illuminate\Support\Facades\Auth;
use Mockery\Expectation;

class TaskGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $taskGroupRepository;
    public function __construct(TaskGroupRepository $taskGroupRepository){
        $this->taskGroupRepository = $taskGroupRepository;
        $this->middleware('admin');
    }
    public function index()
    {
        try{
            $taskGroups = $this->taskGroupRepository->getAll();
            return ApiResponse::success($taskGroups,'Get all task groups successfully',200);
        }
        catch(\Exception $e){
            return ApiResponse::error('Error',500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
