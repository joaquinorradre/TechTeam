<?php

namespace App\Http\Controllers;

use App\Serializers\StreamsDataSerializer;
use App\Services\GetStreamsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
        try {
            $streams = $this->getStreamsService->execute();
            $serializedStreams = $this->streamsSerializer->serialize($streams);
            return new JsonResponse($serializedStreams, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Exception $exception) {
            if ($exception->getCode() === Response::HTTP_SERVICE_UNAVAILABLE) {
                return new JsonResponse([
                'error' => 'Service Unavailable',
                'message' => 'No se pueden devolver streams en este momento, inténtalo más tarde'
                ], Response::HTTP_SERVICE_UNAVAILABLE);
            }
            return new JsonResponse([
                'error' => 'Internal Server Error',
                'message' => 'Error del servidor al obtener los streams'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
