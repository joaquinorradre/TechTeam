<?php

namespace App\Services;

use Exception;

class GetTimelineService
{
    private TimelineDataManager $timelineDataManager;

    public function __construct(TimelineDataManager $timelineDataManager)
    {
        $this->timelineDataManager = $timelineDataManager;
    }

    /**
     * @throws Exception
     */
    public function execute($userId)
    {
        $timelineResponse = $this->timelineDataManager->getTimeline($userId);

        $response = json_decode($timelineResponse, true);

        if ($response && isset($response['data'])) {
            return $response['data'];
        } else {
            return [];
        }
    }

}