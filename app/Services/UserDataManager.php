<?php

namespace App\Services;

use App\Clients\ApiClient;

class UserDataManager
{
    private string $token;
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getUserData($api_url): string
    {
        $this->getTokenTwitch();
        return $this->apiClient->makeCurlCall($api_url,$this->token);
    }

    public function getTokenTwitch(): string
    {
        $url = 'https://id.twitch.tv/oauth2/token';

        $response = $this->apiClient->getToken($url);
        $result = json_decode($response, true);

        if(isset($result['access_token'])){
            $this->token = $result['access_token'];
        }

        return $this->token;
    }
}