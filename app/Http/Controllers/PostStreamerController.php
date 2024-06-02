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

    public function __invoke(Request $request)
    {
        $userId = $request->input('userId');
        $streamerId = $request->input('streamerId');
        $result = $this->postStreamerService->execute($userId, $streamerId);

        if ($result) {
            $response = ["message" => "Ahora sigues a $streamerId"];
            return new JsonResponse($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $response = ["message" => "No sigues a $streamerId"];
        return new JsonResponse($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
