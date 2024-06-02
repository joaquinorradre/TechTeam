<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Serializers\TimelineSerializer;
use App\Serializers\UserListSerializer;
use Exception;

class TimelineDataManager
{
    private DBClient $dBClient;
    private TwitchTokenService $twitchTokenService;
    private ApiClient $apiClient;

    public function __construct(DBClient $dBClient, TwitchTokenService $twitchTokenService, ApiClient $apiClient)
    {
        $this->dBClient = $dBClient;
        $this->twitchTokenService = $twitchTokenService;
        $this->apiClient = $apiClient;
    }

    /**
     * @throws Exception
     */
    public function getTimeline(string $userId): array
    {
        try {
            $twitchToken = $this->twitchTokenService->getToken();

            $followedStreamers = $this->dBClient->getFollowedStreamers($userId);
            if (empty($followedStreamers)) {
                return [];
            }

            $allVideos = [];

            foreach ($followedStreamers as $streamer) {
                $queryParams = http_build_query(['user_id' => $streamer->streamerId, 'first' => 5]);
                $response = $this->apiClient->makeCurlCall("https://api.twitch.tv/helix/streams?$queryParams", $twitchToken);

                if ($response['status'] !== 200) {
                    throw new Exception('Error al obtener los videos del streamer', $response['status']);
                }

                $videosData = json_decode($response['response'], true)['data'];

                foreach ($videosData as $video) {
                    $allVideos[] = [
                        'streamerId' => $video['user_id'],
                        'streamerName' => $video['user_name'],
                        'title' => $video['title'],
                        'game' => $video['game_name'],
                        'viewerCount' => $video['viewer_count'],
                        'startedAt' => $video['created_at']
                    ];
                }
            }

            usort($allVideos, fn($a, $b) => strtotime($b['startedAt']) - strtotime($a['startedAt']));

            return UserListSerializer::serialize($allVideos);
        } catch (Exception $exception) {
            throw new Exception("Error al obtener el timeline: " . $exception->getMessage());
        }
    }
}
