<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetUsersRequest;
use App\Serializers\TimelineDataSerializer;
use App\Services\GetTimelineService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function __invoke(GetUsersRequest $request): JsonResponse
    {
        $userId = $request->input('id');
        $Timeline = $this->getTimelineService->execute($userId);
        $serializedTimeline = $this->TimelineSerializer->serialize($Timeline);

        return new JsonResponse($serializedTimeline, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

}