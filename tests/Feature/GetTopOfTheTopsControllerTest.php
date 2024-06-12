<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Http\Controllers\GetTopOfTheTopsController;
use App\Serializers\TopsOfTheTopsDataSerializer;
use App\Services\GetTopOfTheTopsService;
use App\Services\TopsOfTheTopsDataManager;
use App\Services\TwitchTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetTopOfTheTopsControllerTest extends TestCase
{
    protected $apiClientMock;
    protected $dbClientMock;
    protected $twitchTokenServiceMock;
    protected $topsOfTheTopsDataManagerMock;
    protected $getTopOfTheTopsServiceMock;
    protected $topsOfTheTopsDataSerializerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $this->topsOfTheTopsDataManagerMock = Mockery::mock(TopsOfTheTopsDataManager::class);
        $this->getTopOfTheTopsServiceMock = Mockery::mock(GetTopOfTheTopsService::class);
    }

    /**
     * @test
     */
    public function getTopOfTheTops()
    {
        $request = Request::create('/analytics/tops', 'GET', ['since' => 600]);

        $gamesData = [
            'data' => [
                ['id' => '123', 'name' => 'Game 1'],
                ['id' => '456', 'name' => 'Game 2'],
                ['id' => '789', 'name' => 'Game 3'],
            ]
        ];

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode($gamesData), 'status' => 200]);

        $this->dbClientMock
            ->shouldReceive('fetchGames')
            ->andReturn(collect([]))
            ->shouldReceive('insertGame')
            ->withAnyArgs()
            ->andReturnNull()
            ->shouldReceive('getGameData')
            ->andReturn($gamesData['data']);

        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('token');

        $this->topsOfTheTopsDataManagerMock
            ->shouldReceive('updateGamesData')
            ->andReturn($gamesData['data'])
            ->shouldReceive('updateExistingGamesData')
            ->with(600)
            ->andReturn($gamesData['data'])
            ->shouldReceive('fetchGames')
            ->andReturn(collect($gamesData['data']));

        $this->getTopOfTheTopsServiceMock
            ->shouldReceive('execute')
            ->with(600)
            ->andReturn($gamesData['data']);

        $this->topsOfTheTopsDataSerializerMock
            ->shouldReceive('serialize')
            ->with($gamesData['data'])
            ->andReturn($gamesData['data']);

        $controller = new GetTopOfTheTopsController(
            $this->getTopOfTheTopsServiceMock,
            $this->topsOfTheTopsDataSerializerMock
        );

        $response = $controller($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode($gamesData['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $response->getContent()
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
