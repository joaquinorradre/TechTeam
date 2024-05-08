<?php

namespace App\Http\Clients;


class ApiClient
{
    private const string CLIENT_ID = 'szp2ugo2j6edjt8ytdak5n2n3hjkq3';
    private const string CLIENT_SECRET = '07gk0kbwwzpuw2uqdzy1bjnsz9k32k';
    private string $url = 'https://id.twitch.tv/oauth2/token';

    public function getTokenFromAPI(): string
    {
        $data = array(
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'grant_type' => 'client_credentials'
        );
        $curlHeaders = curl_init();
        curl_setopt($curlHeaders,CURLOPT_URL,$this->url);
        curl_setopt($curlHeaders,CURLOPT_POST,1);
        curl_setopt($curlHeaders,CURLOPT_POSTFIELDS,http_build_query($data));
        curl_setopt($curlHeaders,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curlHeaders,CURLOPT_HTTPHEADER,array('Content-Type: application/x-www-form-urlencoded'));

        $response = curl_exec($curlHeaders);

        if(curl_errno($curlHeaders)) {
            echo 'Error en la petición cURL para obtener el token: ' . curl_error($curlHeaders);
            exit;
        }

        curl_close($curlHeaders);

        return $response;
    }

    public function makeCurlCall($api_url, $token): array
    {
        $curlHeaders = curl_init();

        $api_headers = array(
            'Client-ID: ' . self::CLIENT_ID,
            'Authorization: Bearer ' . $token,
        );

        curl_setopt($curlHeaders,CURLOPT_URL,$api_url);
        curl_setopt($curlHeaders,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curlHeaders, CURLOPT_HTTPHEADER, $api_headers);
        $api_response = curl_exec($curlHeaders);
        $http_status = curl_getinfo($curlHeaders, CURLINFO_HTTP_CODE);

        if(curl_errno($curlHeaders)) {
            echo 'Error in cURL request to get live streams: ' . curl_error($curlHeaders);
            exit;
        }
        curl_close($curlHeaders);

        return ['response' => $api_response, 'status' => $http_status];
    }
}