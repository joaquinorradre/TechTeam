<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class TimelineDataManager
{
    private ApiClient $apiClient;
    private TwitchTokenService $twitchTokenService;
    private const string API_FOLLOWED_URL = 'https://api.twitch.tv/helix/channels/followed';
    private const string API_STREAMS_URL = 'https://api.twitch.tv/helix/streams';

    public function __construct(ApiClient $apiClient, TwitchTokenService $twitchTokenService)
    {
        $this->apiClient = $apiClient;
        $this->twitchTokenService = $twitchTokenService;
    }

    /**
     * @throws Exception
     */
    public function getTimeline(string $userId): string
    {
        try {
            $twitchToken = $this->twitchTokenService->getToken();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }

        try {
            $followedResult = $this->apiClient->makeCurlCall(self::API_FOLLOWED_URL . "?user_id=$userId", $twitchToken);
            $followedResponse = json_decode($followedResult['response'], true);
            var_dump($followedResponse);

            if (!isset($followedResponse['data'])) {
                throw new Exception('No se pudieron obtener los broadcasters seguidos.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $followedIds = array_column($followedResponse['data'], 'broadcaster_id');

            $streams = array();

            foreach ($followedIds as $broadcasterId) {

                $streamsResult = $this->apiClient->makeCurlCall(self::API_STREAMS_URL . "?user_id=$broadcasterId&first=5", $twitchToken);
                $streamsResponse = json_decode($streamsResult['response'], true);

                if (isset($streamsResponse['data'])) {
                    $streams = array_merge($streams, $streamsResponse['data']);
                }
            }

            usort($streams, function($a, $b) {
                return strtotime($b['started_at']) - strtotime($a['started_at']);
            });

            return json_encode($streams);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

}