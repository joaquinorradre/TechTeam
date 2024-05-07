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
        $apiClient = Mockery::mock(ApiClient::class);

        $this->app
            ->when(StreamsManager::class)
            ->needs(ApiClient::class)
            ->give(fn() => $apiClient);

        $getTokenExpectedResponse = json_encode([
            'access_token' => 'zfmr6i7cbwken2maslfu9v89tvq9ne',
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
            ->with('https://api.twitch.tv/helix/streams', [0 => "Authorization: Bearer zfmr6i7cbwken2maslfu9v89tvq9ne"])
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $response = $this->get('/analytics/streams');
        //dd($response->getContent());
        $response->assertStatus(200);

        $response->assertJsonFragment([
            "title" => "【#ストグラ 56日目】レダーヨージロー鳩禁指示禁",
            "user_name" => "らっだぁ"
        ]);



    }
}
