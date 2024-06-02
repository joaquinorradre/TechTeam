<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class TimelineDataManager
{
    protected DBClient $dbClient;
    protected TwitchTokenService $twitchTokenService;
    protected ApiClient $apiClient;

    public function __construct(DBClient $dbClient, TwitchTokenService $twitchTokenService, ApiClient $apiClient)
    {
        $this->dbClient = $dbClient;
        $this->twitchTokenService = $twitchTokenService;
        $this->apiClient = $apiClient;
    }

    /**
     * @throws Exception
     */
    public function getTimeline(string $userId): array
    {
        try {
            $token = $this->twitchTokenService->getToken();
            $streamers = $this->dbClient->getFollowedStreamers($userId);

            if (empty($streamers)) {
                throw new Exception("El usuario especificado {$userId} no sigue a ningún streamer", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $timeline = $this->fetchStreamersTimeline($streamers, $token);
            if (empty($timeline)) {
                throw new Exception("No se encontraron streams para los streamers seguidos por el usuario.", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $timeline;
        } catch (Exception $e) {
            $statusCode = $e->getCode() === 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            throw new Exception("Error al obtener el timeline: " . $e->getMessage(), $statusCode);
        }
    }

    /**
     * @throws Exception
     */
    private function fetchStreamersTimeline(array $streamers, string $token): array
    {
        $timeline = [];

        foreach ($streamers as $streamer) {
            $response = $this->apiClient->makeCurlCall(
                "https://api.twitch.tv/helix/streams?user_id={$streamer->streamerId}&first=5",
                $token
            );

            if ($response['status'] === Response::HTTP_OK) {
                $data = json_decode($response['response'], true);
                foreach ($data['data'] as $stream) {
                    $timeline[] = [
                        'streamerId' => $stream['user_id'],
                        'userName' => $stream['user_name'],
                        'title' => $stream['title'],
                        'gameName' => $stream['game_name'],
                        'viewerCount' => $stream['viewer_count'],
                        'startedAt' => $stream['started_at'],
                    ];
                }
            } else {
                throw new Exception('Error al obtener los videos del streamer: ' . $streamer->streamerId);
            }
        }

        usort($timeline, fn($a, $b) => strtotime($b['startedAt']) - strtotime($a['startedAt']));

        return $timeline;
    }
}
