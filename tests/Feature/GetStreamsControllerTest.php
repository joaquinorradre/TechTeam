<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Http\Controllers\GetStreamsController;
use App\Serializers\StreamsDataSerializer;
use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use App\Services\TwitchTokenService;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetStreamsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getStreams()
    {
        $request = Request::create('/analytics/streams', 'GET');
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $dbClientMock = Mockery::mock(DbClient::class);
        $streamsDataSerializerMock = Mockery::mock(StreamsDataSerializer::class);
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
            ->with([['title' => 'Stream 1', 'user_name' => 'User 1']])
            ->andReturn([['title' => 'Stream 1', 'user_name' => 'User 1']]);
        $streamsDataManager = new StreamsDataManager($apiClientMock,$twitchTokenServiceMock);
        $getStreamsService = new GetStreamsService($streamsDataManager);
        $getStreamsController = new GetStreamsController($getStreamsService, $streamsDataSerializerMock);

        $result = $getStreamsController->__invoke($request);

        $this->assertNotEmpty($result);
    }
}