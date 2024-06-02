<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use Exception;

class StreamerExistManager
{
    private const string API_STREAMER_URL = 'https://api.twitch.tv/helix/users';
    private TwitchTokenService $twitchTokenService;
    private ApiClient $apiClient;
    public function __construct(TwitchTokenService $twitchTokenService,ApiClient $apiClient)
    {
        $this->twitchTokenService = $twitchTokenService;
        $this->apiClient = $apiClient;
    }

    public function getStreamer(string $streamerId): bool
    {
        try {
            $token = $this->twitchTokenService->getToken();
            $userIdLink = self::API_STREAMER_URL . "?id=$streamerId";

            $apiResponse = $this->apiClient->makeCurlCall($userIdLink, $token);

            $status = $apiResponse['status'];
            $respuesta = $apiResponse['response'];

            if ($status >= 200 && $status < 300) {
                $responseData = json_decode($respuesta, true);
                return !empty($responseData['data']);
            }

            throw new Exception('Error del servidor al seguir al streamer', $status);
        } catch (Exception $exception) {
            throw $exception;
        }
    }


}