<?php

namespace Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Http\Controllers\GetStreamsController;
use App\Serializers\StreamsDataSerializer;
use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use App\Services\TwitchTokenService;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetUsersFollowControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getStreams()
    {
        $request = Request::create('/analytics/streams', 'GET', ['parametro' => 'valor']);

        $apiClientMock = Mockery::mock(ApiClient::class);
        $streamsDataManagerMock = Mockery::mock(StreamsDataManager::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $dbClientMock = Mockery::mock(DbClient::class);
        $getStreamsServiceMock = Mockery::mock(GetStreamsService::class);
        $streamsDataSerializerMock = Mockery::mock(StreamsDataSerializer::class);

        $getStreamsServiceMock
            ->shouldReceive('execute')
            ->once()
            ->andReturn(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]);

        $streamsDataManagerMock
            ->shouldReceive('getStreams')
            ->once()
            ->andReturn(json_encode(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]));

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('token');

        $dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]), 'status' => 200]);

        $streamsDataSerializerMock
            ->shouldReceive('serialize')
            ->once()
            ->with(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]])
            ->andReturn([['title' => 'Stream 1', 'user_name' => 'User 1',]]);

        $getStreamsController = new GetStreamsController($getStreamsServiceMock,$streamsDataSerializerMock);

        $result = $getStreamsController->__invoke($request);

        $this->assertNotEmpty($result);
    }

}