<?php

namespace App\Http\Controllers;

use App\Serializers\StreamsDataSerializer;
use App\Services\GetStreamsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GetStreamsController extends Controller
{
    private GetStreamsService $getStreamsService;
    private StreamsDataSerializer $streamsSerializer;
    public function __construct(GetStreamsService $getStreamsService, StreamsDataSerializer $streamsSerializer)
    {
        $this->getStreamsService = $getStreamsService;
        $this->streamsSerializer = $streamsSerializer;
    }

    /**
     * Handle the incoming request
     * @throws \Exception
     */
    public function __invoke(Request $request): JsonResponse
    {
        $streams = $this->getStreamsService->execute();
        $serializedStreams = $this->streamsSerializer->serialize($streams);

        return new JsonResponse($serializedStreams, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
