<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\ApiClient;
use App\Services\StreamsManager;
use Mockery;
use Tests\TestCase;

class GetStreamsTest extends TestCase
{
    /**
     * @test
     **/

    public function gets_streams()
    {
        $accessToken = 'dummy_access_token';

        $apiClient = Mockery::mock(ApiClient::class);

        $this->app
            ->when(StreamsManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $apiClient);

        $getTokenExpectedResponse = json_encode([
            'access_token' => $accessToken,
            'expires_in' => 5443987,
            'token_type' => 'bearer'
        ]);

        $getStreamsExpectedResponse = json_encode(['data' => [[
            'title' => 'Stream title',
            'user_name' => 'user_name',
        ]]]);

        $apiClient
            ->expects('getToken')
            ->with('https://id.twitch.tv/oauth2/token')
            ->once()
            ->andReturn($getTokenExpectedResponse);

        $apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/streams', [0 => "Authorization: Bearer $accessToken"]) // Fix string interpolation
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $response = $this->get('/streams');

        $response->assertStatus(200);

        $response->assertContent('[{"title":"Stream title","user_name":"user_name"}]');
    }
}
