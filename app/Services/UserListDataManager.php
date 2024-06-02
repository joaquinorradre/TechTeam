<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Serializers\UserDataSerializer;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserListDataManager
{
    private ApiClient $apiClient;
    private TwitchTokenService $twitchTokenService;
    private DBClient $dbClient;
    private UserDataSerializer $serializer;

    public function __construct(ApiClient $apiClient, TwitchTokenService $twitchTokenService, DBClient $dbClient, UserDataSerializer $serializer)
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
            $result = [];

            foreach ($usersWithFollowedStreamers as $userWithFollowedStreamers) {
                if (!isset($result[$userWithFollowedStreamers->username])) {
                    $result[$userWithFollowedStreamers->username] = [];
                }

                $response = $this->apiClient->makeCurlCall("https://api.twitch.tv/helix/users?id={$userWithFollowedStreamers->streamerId}", $twitchToken);

                if ($response['status'] !== Response::HTTP_OK) {
                    throw new Exception('Error al obtener el login del streamer', $response['status']);
                }

                $streamerData = json_decode($response['response'], true)['data'];
                if (isset($streamerData[0]['login'])) {
                    $result[$userWithFollowedStreamers->username][] = $streamerData[0]['login'];
                }
            }
            return $this->serializer->serialize($result);

        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
}