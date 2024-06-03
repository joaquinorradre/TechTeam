<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTimelineRequest;
use App\Services\GetTimelineService;
use Exception;
use Illuminate\Http\JsonResponse;

class GetTimelineController
{
    private GetTimelineService $getTimelineService;
    public function __construct(GetTimelineService $getTimelineService)
    {
        $this->getTimelineService = $getTimelineService;
    }

    /**
     * Handle the incoming request
     * @throws Exception
     */
    public function __invoke(GetTimelineRequest $request): JsonResponse
    {
        $userId = $request->input('userId');
        try {
            $timeline = $this->getTimelineService->execute($userId);
            return new JsonResponse($timeline, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }
    }
}
