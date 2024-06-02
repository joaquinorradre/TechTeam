<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Serializers\TimelineSerializer;
use App\Serializers\UserListSerializer;
use Exception;

class TimelineDataManager
{
    protected $dbClient;
    protected $twitchTokenService;
    protected $apiClient;

    public function __construct(DBClient $dbClient, TwitchTokenService $twitchTokenService, ApiClient $apiClient)
    {
        $this->dbClient = $dbClient;
        $this->twitchTokenService = $twitchTokenService;
        $this->apiClient = $apiClient;
    }

    public function getTimeline(string $userId)
    {
        try {
            $token = $this->twitchTokenService->getToken();
            $streamers = $this->dbClient->getFollowedStreamers($userId);

            $timeline = [];

            foreach ($streamers as $streamer) {
                $response = $this->apiClient->makeCurlCall(
                    "https://api.twitch.tv/helix/streams?user_id={$streamer->streamerId}&first=5",
                    $token
                );

                if ($response['status'] === 200) {
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
                    throw new Exception('Error al obtener los videos del streamer');
                }
            }

            return $timeline;
        } catch (Exception $e) {
            throw new Exception('Error al obtener el timeline: ' . $e->getMessage());
        }
    }
}