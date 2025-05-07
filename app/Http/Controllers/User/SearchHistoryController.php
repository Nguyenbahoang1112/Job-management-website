<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Repository\SearchHistory\SearchHistoryRepository;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
class SearchHistoryController extends Controller
{

    protected $searchHistoryRepository;

    public function __construct(SearchHistoryRepository $searchHistoryRepository)
    {
        $this->middleware('auth:sanctum');
        $this->searchHistoryRepository = $searchHistoryRepository;
    }

    public function store(Request $request)
    {

        $userId = $request->user()->id; // Lấy userId từ authentication
        $searchHistory = $this->searchHistoryRepository->createSearchHistory($request->input('search_query'), $userId);

        return ApiResponse::success(
            ['id' => $searchHistory->id, 'search_query' => $searchHistory->search_query],
            'Search history created successfully.',
            ApiResponse::SUCCESS
        );
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $histories = $this->searchHistoryRepository->getSearchHistories($userId);

        if (empty(array_filter($histories))) {
            return ApiResponse::error('No search histories found.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($histories, 'Search histories retrieved successfully.', ApiResponse::SUCCESS);
    }


    public function destroy(int $id,Request $request)
    {
        try {
            $userId = $request->user()->id;
            $this->searchHistoryRepository->deleteSearchHistory($id,$userId);
            return ApiResponse::success([], 'Search history deleted successfully.', ApiResponse::SUCCESS);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), ApiResponse::FORBIDDEN);
        }
    }


}
