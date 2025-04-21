<?php

namespace App\Http\Controllers\User;

use App\Models\Note;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Cache\Store;
use App\Http\Resources\Note\NoteResource;
use App\Http\Repository\Note\NoteRepository;
use App\Http\Requests\User\Note\CreateNoteRequest;

class NoteController extends Controller
{

    protected $noteRepository;

    public function __construct(NoteRepository $noteRepository)
    {
        $this->middleware('auth:sanctum');
        $this->noteRepository = $noteRepository;
    }

    public function index(Request $request)
    {
        $notes = $this->noteRepository->getAllByUser($request->paginate);

        if ($notes) {
            return ApiResponse::success(NoteResource::collection($notes), 'Get notes successful', 200);
        }
        return ApiResponse::error('Get notes failed', 400);
    }

    public function store(CreateNoteRequest $createNoteRequest)
    {
        $noteAttribute = $createNoteRequest->all();
        $noteAttribute['user_id'] = auth('sanctum')->user()->id;

        $noteCreate = $this->noteRepository->create($noteAttribute);
        if ($noteCreate) {
            return ApiResponse::success(new NoteResource($noteCreate), 'Create note successful', 200);
        }
        return ApiResponse::error('Create note failed', 400);
    }

    public function update(Request $request, $id)
    {
        $noteAttribute = $request->only('content');
        $noteUpdate = $this->noteRepository->update($noteAttribute, $id);
        if ($noteUpdate) {
            return ApiResponse::success(new NoteResource($noteUpdate), 'Update note successful', 200);
        }
        return ApiResponse::error('Update note failed', 400);
    }

    public function destroy($id)
    {
        $noteDelete = $this->noteRepository->delete($id);
        if ($noteDelete) {
            return ApiResponse::success(new NoteResource($noteDelete), 'Delete note successful', 200);
        }
        return ApiResponse::error('Delete note failed', 400);

    }
}
