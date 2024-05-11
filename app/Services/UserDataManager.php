<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserDataManager
{
    private ApiClient $apiClient;
    private DBClient $dbClient;
    private TwitchTokenService $twitchTokenService;
    private const string API_USER_URL = 'https://api.twitch.tv/helix/users';

    public function __construct(ApiClient $apiClient, DBClient $dbClient, TwitchTokenService $twitchTokenService)
    {
        $this->apiClient = $apiClient;
        $this->dbClient = $dbClient;
        $this->twitchTokenService = $twitchTokenService;
    }

    public function getUserData($userId): Response
    {
        try {
            $twitchToken = $this->twitchTokenService->getToken();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }

        try {
            $userIdLink = self::API_USER_URL . "?id=$userId";
            $result = $this->apiClient->makeCurlCall($userIdLink, $twitchToken);
            $streamsResponse = $result['response'];
            $statusCode = $result['status'];

            if ($statusCode == Response::HTTP_INTERNAL_SERVER_ERROR) {
                throw new Exception('No se pueden devolver usuarios en este momento, inténtalo más tarde', Response::HTTP_SERVICE_UNAVAILABLE);
            }

            return response($streamsResponse, $statusCode);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

}