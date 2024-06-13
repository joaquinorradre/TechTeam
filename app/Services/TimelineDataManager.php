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
                throw new Exception("No se encontraron streams para los streamers seguidos por el usuario.", Response::HTTP_NOT_FOUND);
            }

            return $timeline;
        } catch (Exception $exception) {
            $statusCode = $exception->getCode() === 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $exception->getCode();

            if (in_array($statusCode, [Response::HTTP_INTERNAL_SERVER_ERROR, Response::HTTP_NOT_FOUND, Response::HTTP_SERVICE_UNAVAILABLE])) {
                throw $exception;
            }

            throw new Exception("Error al obtener el timeline: " . $exception->getMessage(), $statusCode);
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
                "https://api.twitch.tv/helix/videos?user_id={$streamer->streamerId}&sort=time&first=5",
                $token
            );

            if ($response['status'] === Response::HTTP_OK) {
                $data = json_decode($response['response'], true);
                foreach ($data['data'] as $stream) {
                    $timeline[] = [
                        'streamerId' => $stream['user_id'],
                        'userName' => $stream['user_name'],
                        'title' => $stream['title'],
                        'viewerCount' => $stream['view_count'],
                        'startedAt' => $stream['created_at'],
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
