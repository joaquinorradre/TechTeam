<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class StreamsDataManager
{
    private ApiClient $apiClient;
    private TwitchTokenService $twitchTokenService;
    private const string API_STREAMS_URL = 'https://api.twitch.tv/helix/streams';

    public function __construct(ApiClient $apiClient, TwitchTokenService $twitchTokenService)
    {
        $this->apiClient = $apiClient;
        $this->twitchTokenService = $twitchTokenService;
    }

    /**
     * @throws Exception
     */
    public function getStreams(): string
    {
        try {
            $twitchToken = $this->twitchTokenService->getToken();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }

        try {
            $result = $this->apiClient->makeCurlCall(self::API_STREAMS_URL, $twitchToken);
            $streamsResponse = $result['response'];
            $statusCode = $result['status'];

            if ($statusCode == Response::HTTP_INTERNAL_SERVER_ERROR) {
                throw new Exception('No se pueden devolver streams en este momento, inténtalo más tarde', Response::HTTP_SERVICE_UNAVAILABLE);
            }
            return $streamsResponse;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
}