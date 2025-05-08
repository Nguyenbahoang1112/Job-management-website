<?php

namespace App\Http\Repository\SearchHistory;
use App\Models\SearchHistory;
use App\Models\Task;
use App\Models\RepeatRule;
use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;
use App\Models\TaskDetail;
use Carbon\Carbon;

use App\Models\Team;
class SearchHistoryRepository extends BaseRepository{

    public function __construct(SearchHistory $searchHistory)
    {
        parent::__construct($searchHistory);
    }
    public function createSearchHistory(string $searchQuery, int $userId)
    {
        // Kiểm tra xem search_query đã tồn tại cho user_id chưa
        $existingHistory = $this->model
            ->where('user_id', $userId)
            ->where('search_query', $searchQuery)
            ->first();

        if ($existingHistory) {
            // Nếu đã tồn tại, trả về bản ghi hiện có mà không tạo mới
            return $existingHistory;
        }

        // Nếu chưa tồn tại, tạo bản ghi mới
        return $this->create([
            'search_query' => $searchQuery,
            'user_id' => $userId,
        ]);
    }

    public function getSearchHistories($userId)
    {

        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'search_query' => $history->search_query,
                    'user_id' => $history->user_id,
                    'created_at' => $history->created_at->toISOString(),
                    'updated_at' => $history->updated_at->toISOString(),
                ];
            })
            ->toArray();
    }

    public function deleteSearchHistory(int $id, $userId)
    {
        $history = $this->find($id);
        if (!$history || $history->user_id !== $userId) {
            throw new \Exception('Unauthorized to delete this search history.');
        }
        return $this->delete($id);
    }
    public function deleteAllHistory($userId)
    {
        return $this->model->where('user_id', $userId)->delete();
    }
}
