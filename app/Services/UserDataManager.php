<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;

class UserDataManager
{
    private string $token;
    private ApiClient $apiClient;
    private DBClient $dbClient;

    public function __construct(ApiClient $apiClient, DBClient $dbClient)
    {
        $this->apiClient = $apiClient;
        $this->dbClient = $dbClient;

    }

    public function getUserData($userId): string
    {
        $api_url = "https://api.twitch.tv/helix/users?id=$userId";
        $this->getTokenTwitch();
        return $this->apiClient->makeCurlCall($api_url,$this->token);
    }

    public function getTokenTwitch(): string
    {
        $databaseTokenResponse = $this->dbClient->getTokenFromDataBase();

        if ($databaseTokenResponse !== null) {
            return $databaseTokenResponse;
        }

        $apiTokenResponse = $this->apiClient->getTokenFromAPI();
        $result = json_decode($apiTokenResponse, true);

        if (isset($result['access_token'])) {
            $this->token = $result['access_token'];
        }

        return $this->token;
    }
}