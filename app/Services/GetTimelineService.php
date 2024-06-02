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
        return $this->timelineDataManager->getTimeline($userId);
    }

}