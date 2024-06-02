<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTimelineRequest;
use App\Serializers\TimelineDataSerializer;
use App\Services\GetTimelineService;
use Exception;
use Illuminate\Http\JsonResponse;

class GetTimelineController
{
    private GetTimelineService $getTimelineService;
    private TimelineDataSerializer $TimelineSerializer;
    public function __construct(GetTimelineService $getTimelineService, TimelineDataSerializer $TimelineSerializer)
    {
        $this->getTimelineService = $getTimelineService;
        $this->TimelineSerializer = $TimelineSerializer;
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
