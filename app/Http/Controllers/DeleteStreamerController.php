<?php

namespace App\Http\Controllers;

use App\Services\DeleteStreamerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteStreamerController extends Controller
{
    private DeleteStreamerService $unfollowService;

    public function __construct(DeleteStreamerService $unfollowService)
    {
        $this->unfollowService = $unfollowService;
    }

    public function __invoke(Request $request)
    {
        $userId = $request->input('userId');
        $streamerId = $request->input('streamerId');
        $result = $this->unfollowService->execute($userId, $streamerId);

        if ($result) {
            $response = ["message" => "Has dejado de seguir a $streamerId"];
            return new JsonResponse($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $response = ["message" => "No has podido deja de seguir a $streamerId"];
        return new JsonResponse($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
