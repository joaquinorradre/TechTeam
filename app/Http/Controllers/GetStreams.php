<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetUsersRequest;
use App\Services\GetStreamsService;
use App\Services\NewTwitchApi;
use Illuminate\Http\Request;

class GetStreams extends Controller
{
    private GetStreamsService $getStreamsService;

    public function __construct(GetStreamsService $getStreamsService)
    {
        $this->getStreamsService = $getStreamsService;
    }

    /**
     * Handle the incoming request
     */
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $streams = $this->getStreamsService->execute();

        return response()->json($streams, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

