<?php

namespace App\Services;

class GetStreamsService
{
    private StreamsDataManager $streamsManager;

    public function __construct(StreamsDataManager $streamsManager)
    {
        $this->streamsManager = $streamsManager;
    }

    public function execute()
    {
        $streams = $this->streamsManager->getStreams('https://api.twitch.tv/helix/streams');
        $response = json_decode($streams, true);
        $filtered_streams = [];

        if (isset($response['data'])) {
            foreach ($response['data'] as $stream) {
                $filtered_streams[] = [
                    'title' => $stream['title'],
                    'user_name' => $stream['user_name']
                ];
            }
        }

        return($filtered_streams);
    }

}