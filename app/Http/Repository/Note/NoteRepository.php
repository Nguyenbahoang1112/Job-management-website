<?php

namespace App\Http\Repository\Note;

use App\Http\Repository\BaseRepository;
use App\Models\Note;

class NoteRepository extends BaseRepository
{
    public function __construct(Note $note)
    {
        parent::__construct($note);
    }
    public function getAllByUser($paginate)
    {
        $userId = auth('sanctum')->user()->id;

        return $this->model
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'asc')
                    ->paginate($paginate);
    }

    public function update($attributes = [], $id)
    {
        $note = $this->find($id); // Tìm bản ghi hiện tại
        if ($note) {
            // Kiểm tra nếu giá trị content có thay đổi
            if ($note->content != $attributes['content']) {
                $note->update([
                    'content' => $attributes['content']
                ]);
                return $note;
            }
        }
        return false;
    }


}
