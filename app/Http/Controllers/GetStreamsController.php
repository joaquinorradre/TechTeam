<?php

namespace App\Http\Controllers;

use App\Serializers\StreamsDataSerializer;
use App\Services\GetStreamsService;
use Illuminate\Http\Request;

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
     */
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {

        $streams = $this->getStreamsService->execute();
        $serializedStreams = $this->streamsSerializer->serialize($streams);

        return response()->json($serializedStreams, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
