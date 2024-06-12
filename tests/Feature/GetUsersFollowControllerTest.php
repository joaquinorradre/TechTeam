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

class GetUsersFollowControllerTest extends TestCase
{
    protected $apiClientMock;
    protected $dbClientMock;
    protected $twitchTokenServiceMock;
    protected $streamsDataSerializerMock;
    protected $streamsDataManager;
    protected $getStreamsService;
    protected $getStreamsController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->dbClientMock = Mockery::mock(DbClient::class);
        $this->twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $this->streamsDataSerializerMock = Mockery::mock(StreamsDataSerializer::class);

        $this->streamsDataManager = new StreamsDataManager($this->apiClientMock, $this->twitchTokenServiceMock);
        $this->getStreamsService = new GetStreamsService($this->streamsDataManager);
        $this->getStreamsController = new GetStreamsController($this->getStreamsService, $this->streamsDataSerializerMock);
    }

    /**
     * @test
     */
    public function getStreams()
    {
        $request = Request::create('/analytics/streams', 'GET', ['parametro' => 'valor']);
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('token');
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]), 'status' => 200]);
        $this->streamsDataSerializerMock
            ->shouldReceive('serialize')
            ->once()
            ->with([['title' => 'Stream 1', 'user_name' => 'User 1']])
            ->andReturn([['title' => 'Stream 1', 'user_name' => 'User 1',]]);

        $result = $this->getStreamsController->__invoke($request);

        $this->assertNotEmpty($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
