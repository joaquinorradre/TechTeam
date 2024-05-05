<?php

namespace App\Services;

class StreamsManager
{
    private $token;
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getStreams($api_url): string
    {
        $this->getTokenTwitch();

        $response = $this->apiClient->makeCurlCall($api_url,$this->token);
        return $response;
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