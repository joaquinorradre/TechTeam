<?php

namespace App\Services;

use App\Clients\ApiClient;

class NewTwitchApi
{
    private string $token;
    private ApiClient $apiClient;


    public function __construct(ApiClient $curlExecutor)
    {
        $this->apiClient = $curlExecutor;
        $this->token = $this->getTokenTwitch();
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