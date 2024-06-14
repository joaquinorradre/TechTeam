<?php

namespace App\Http\Controllers;

use App\Services\PostStreamerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostStreamerController extends Controller
{
    private PostStreamerService $postStreamerService;

    public function __construct(PostStreamerService $postStreamerService)
    {
        $this->postStreamerService = $postStreamerService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->input('userId');
        $streamerId = $request->input('streamerId');
        try {
            $this->postStreamerService->execute($userId, $streamerId);
            $response = ["message" => "Ahora sigues a $streamerId"];
            return new JsonResponse($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $exception) {
            $response = ["message" => $exception->getMessage()];
            return new JsonResponse($response, $exception->getCode(), [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }
}
