<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Serializers\StreamsDataSerializer;
use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use App\Services\TwitchTokenService;
use Mockery;
use Tests\TestCase;

class GetStreamsControllerTest extends TestCase
{
    public function testIntegrationGetStreams()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $streamDataManagerMock = Mockery::mock(StreamsDataManager::class);
        $streamDataSerializerMock = Mockery::mock(StreamsDataSerializer::class);
        $getStreamServiceMock = Mockery::mock(GetStreamsService::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $getStreamsExpectedResponse = json_encode(['data' => [['title' => 'Stream title', 'user_name' => 'user_name']]]);
        $getTokenExpectedResponse = 'mocked_access_token';

        $getStreamServiceMock
            ->shouldReceive('execute')
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $streamDataManagerMock
            ->shouldReceive('getStreams')
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($getTokenExpectedResponse);

        $dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->with('https://id.twitch.tv/oauth2/token')
            ->once()
            ->andReturn($getTokenExpectedResponse);

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with('https://api.twitch.tv/helix/streams', $getTokenExpectedResponse)
            ->once()
            ->andReturn($getStreamsExpectedResponse);

        $streamDataSerializerMock
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(json_decode($getStreamsExpectedResponse, true)['data']);

        $response = $this->get('/analytics/streams');

        // Aserciones
        $response->assertStatus(500);
        $response->assertJson([['title' => 'Stream title', 'user_name' => 'user_name']]);
    }
}
