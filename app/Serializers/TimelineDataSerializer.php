<?php

namespace App\Serializers;

class TimelineDataSerializer
{
    public function serialize(array $timelineData): array
    {
        $serializedTimelineData = [];

        foreach ($timelineData as $timeline) {
            $serializedTimelineData[] = [
                'streamerId' => $timeline['user_id'],
                'streamerName' => $timeline['user_name'],
                'title' => $timeline['title'],
                'game' => $timeline['game_name'],
                'viewerCount' => $timeline['viewer_count'],
                'startedAt' => $timeline['started_at'],
                
            ];
        }
        return $serializedTimelineData;
    }

}