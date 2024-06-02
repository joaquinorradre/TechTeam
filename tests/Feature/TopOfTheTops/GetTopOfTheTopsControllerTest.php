<?php

namespace Tests\Feature\TopOfTheTops;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Http\Controllers\GetTopOfTheTopsController;
use App\Services\GetTopOfTheTopsService;
use App\Services\TopsOfTheTopsDataManager;
use App\Serializers\TopsOfTheTopsDataSerializer;
use App\Services\TwitchTokenService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;

class GetTopOfTheTopsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getTopOfTheTops()
    {
        $request = Request::create('/analytics/tops', 'GET', ['since' => 600]);

        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $topsOfTheTopsDataManagerMock = Mockery::mock(TopsOfTheTopsDataManager::class);
        $getTopOfTheTopsServiceMock = Mockery::mock(GetTopOfTheTopsService::class);
        $topsOfTheTopsDataSerializerMock = Mockery::mock(TopsOfTheTopsDataSerializer::class);

        $gamesData = [
            'data' => [
                ['id' => '123', 'name' => 'Game 1'],
                ['id' => '456', 'name' => 'Game 2'],
                ['id' => '789', 'name' => 'Game 3'],
            ]
        ];

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode($gamesData), 'status' => 200]);

        $dbClientMock
            ->shouldReceive('fetchGames')
            ->andReturn(collect([]))
            ->shouldReceive('insertGame')
            ->withAnyArgs()
            ->andReturnNull()
            ->shouldReceive('getGameData')
            ->andReturn($gamesData['data']);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('token');

        $topsOfTheTopsDataManagerMock
            ->shouldReceive('updateGamesData')
            ->andReturn($gamesData['data'])
            ->shouldReceive('updateExistingGamesData')
            ->with(600)
            ->andReturn($gamesData['data'])
            ->shouldReceive('fetchGames')
            ->andReturn(collect($gamesData['data']));

        $getTopOfTheTopsServiceMock
            ->shouldReceive('execute')
            ->with(600)
            ->andReturn($gamesData['data']);

        $topsOfTheTopsDataSerializerMock
            ->shouldReceive('serialize')
            ->with($gamesData['data'])
            ->andReturn($gamesData['data']);

        $controller = new GetTopOfTheTopsController(
            $getTopOfTheTopsServiceMock,
            $topsOfTheTopsDataSerializerMock
        );

        $response = $controller($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode($gamesData['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $response->getContent()
        );
    }
}