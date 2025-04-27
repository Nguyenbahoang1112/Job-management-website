<?php

namespace App\Http\Repository\TaskTag;

use App\Models\TaskTag;
use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;

class TaskTagRepository extends BaseRepository
{
    public function __construct(TaskTag $taskTag)
    {
        parent::__construct($taskTag);
    }


}
