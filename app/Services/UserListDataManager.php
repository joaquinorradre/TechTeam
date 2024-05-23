<?php

namespace App\Services;
use Exception;

class UserListDataManager
{
    private ApiClient $apiClient;
    private TwitchTokenService $twitchTokenService;

    public function __construct(ApiClient $apiClient, TwitchTokenService $twitchTokenService)
    {
        $this->apiClient = $apiClient;
        $this->twitchTokenService = $twitchTokenService;
    }

    public function getUserData(): array
    {
        try {
            $twitchToken = $this->twitchTokenService->getToken();

            $response = $this->apiClient->makeCurlCall('https://api.twitch.tv/helix/users', $twitchToken);

            if ($response['status'] !== 200) {
                throw new Exception('Error al obtener la lista de usuarios', $response['status']);
            }

            $userData = json_decode($response['response'], true)['data'];

            //Aquí almacenaremos los datos de usuarios y streamers
            $usersData = [];

            // Iterar sobre cada usuario
            foreach ($userData as $user) {
                // Guardar el id del usuario
                $userId = $user['id'];

                $followedChannelsResponse = $this->apiClient->makeCurlCall("https://api.twitch.tv/helix/users/follows?from_id=$userId", $twitchToken);

                if ($followedChannelsResponse['status'] !== 200) {
                    throw new Exception('Error al obtener los streamers seguidos por el usuario ' . $user['login'], $followedChannelsResponse['status']);
                }

                // Decodificar la respuesta JSON y obtener los datos de los streamers seguidos por el usuario
                $followedChannelsData = json_decode($followedChannelsResponse['response'], true)['data'];

                // Obtener solo el broadcaster_login de cada broadcaster seguido por el usuario
                $followedBroadcasters = array_map(function ($channel) {
                    return $channel['broadcaster_login'];
                }, $followedChannelsData);

                $usersData[] = [
                    'id' => $userId,
                    'followedBroadcasters' => $followedBroadcasters
                ];
            }

            return $usersData;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
}