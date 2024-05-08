<?php

namespace App\Services;

class GetStreamsService
{
    private StreamsDataManager $streamsDataManager;

    public function __construct(StreamsDataManager $streamsDataManager)
    {
        $this->streamsDataManager = $streamsDataManager;
    }

    public function execute()
    {
        $streams = $this->streamsDataManager->getStreams();
        $response = json_decode($streams, true);

        return($response['data']);
    }

}