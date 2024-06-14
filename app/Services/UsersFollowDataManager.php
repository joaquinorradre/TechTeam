<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Serializers\UserDataSerializer;
use App\Serializers\UsersFollowSerializer;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UsersFollowDataManager
{
    private ApiClient $apiClient;
    private TwitchTokenService $twitchTokenService;
    private DBClient $dbClient;
    private UsersFollowSerializer $serializer;

    public function __construct(ApiClient $apiClient, TwitchTokenService $twitchTokenService, DBClient $dbClient, UsersFollowSerializer $serializer)
    {
        $this->apiClient = $apiClient;
        $this->twitchTokenService = $twitchTokenService;
        $this->dbClient = $dbClient;
        $this->serializer = $serializer;
    }

    /**
     * @throws Exception
     */
    public function getUserData(): array
    {
        try {
            $twitchToken = $this->twitchTokenService->getToken();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }

        try {
            $usersWithFollowedStreamers = $this->dbClient->getUsersWithFollowedStreamers();
            if (empty($usersWithFollowedStreamers)) {
                return $this->serializer->serialize([]);
            }

            $result = [];

            foreach ($usersWithFollowedStreamers as $userWithFollowedStreamers) {
                if (!isset($result[$userWithFollowedStreamers->username])) {
                    $result[$userWithFollowedStreamers->username] = [];
                }

                try {
                    $response = $this->apiClient->makeCurlCall("https://api.twitch.tv/helix/users?id={$userWithFollowedStreamers->streamerId}", $twitchToken);

                    if ($response['status'] !== Response::HTTP_OK) {
                        throw new Exception('Error al obtener el login del streamer', $response['status']);
                    }

                    $streamerData = json_decode($response['response'], true)['data'];
                    if (isset($streamerData[0]['login'])) {
                        $result[$userWithFollowedStreamers->username][] = $streamerData[0]['login'];
                    }
                } catch (Exception $e) {
                    $result[$userWithFollowedStreamers->username] = [];
                }
            }

            foreach ($result as $username => &$streamers) {
                if (empty($streamers)) {
                    $streamers = [];
                }
            }

            return $this->serializer->serialize($result);

        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
}
