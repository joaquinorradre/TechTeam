<?php

namespace App\Services;

use App\Clients\ApiClient;
use App\Clients\DBClient;

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

    public function getUserData($api_url): string
    {
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