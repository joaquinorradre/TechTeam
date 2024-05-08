<?php

namespace App\Services;
use Illuminate\Http\Response;
class GetStreamsService
{
    private StreamsDataManager $streamsDataManager;

    public function __construct(StreamsDataManager $streamsDataManager)
    {
        $this->streamsDataManager = $streamsDataManager;
    }

    public function execute()
    {
        $streamsResponse = $this->streamsDataManager->getStreams();

        // Decodificar la respuesta JSON
        $response = json_decode($streamsResponse, true);

        // Verificar si la respuesta contiene datos
        if ($response && isset($response['data'])) {
            return $response['data'];
        } else {
            return [];
        }
    }


}