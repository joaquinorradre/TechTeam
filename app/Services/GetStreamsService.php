<?php

namespace App\Services;
use Exception;

class GetStreamsService
{
    private StreamsDataManager $streamsDataManager;

    public function __construct(StreamsDataManager $streamsDataManager)
    {
        $this->streamsDataManager = $streamsDataManager;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $streamsResponse = $this->streamsDataManager->getStreams();

        $response = json_decode($streamsResponse, true);

        if ($response && isset($response['data'])) {
            return $response['data'];
        } else {
            return [];
        }
    }
}