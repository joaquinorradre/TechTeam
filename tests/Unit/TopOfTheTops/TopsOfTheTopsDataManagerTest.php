<?php

namespace Tests\Unit\TopOfTheTops;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TopsOfTheTopsDataManager;
use App\Services\TwitchTokenService;
use Mockery;
use PHPUnit\Framework\TestCase;

class TopsOfTheTopsDataManagerTest extends TestCase
{

    /**
     * @test
     */
    public function fetchGamesReturnsCollection()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $dbClientMock
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn(collect(['game1', 'game2', 'game3']));

        $dataManager = new TopsOfTheTopsDataManager($dbClientMock, $apiClientMock, $twitchTokenServiceMock);
        $result = $dataManager->fetchGames();

        $this->assertCount(3, $result);
    }

    /**
     * @test
     */
    public function updateGamesDataUpdatesAndReturnsGameData()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with('https://api.twitch.tv/helix/games/top?first=3', 'token')
            ->once()
            ->andReturn(['response' => json_encode(['data' => [['id' => '123', 'name' => 'Game 1']]]), 'status' => 200]);

        $dbClientMock
            ->shouldReceive('getGameData')
            ->andReturn(collect(['gameData']));

        $dataManagerMock = Mockery::mock(TopsOfTheTopsDataManager::class, [$dbClientMock, $apiClientMock, $twitchTokenServiceMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $dataManagerMock
            ->shouldReceive('insertGames')
            ->once();

        $dataManagerMock
            ->shouldReceive('fetchAndInsertVideos')
            ->once();

        $result = $dataManagerMock->updateGamesData();

        $this->assertNotEmpty($result);
        $this->assertEquals(collect(['gameData']), $result);
    }


    /**
     * @test
     */
    public function updateExistingGamesDataUpdatesAndReturnsGameData()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with('https://api.twitch.tv/helix/games/top?first=3', 'token')
            ->once()
            ->andReturn(['response' => json_encode(['data' => [['id' => '123', 'name' => 'Game 1']]]), 'status' => 200]);

        $dbClientMock
            ->shouldReceive('deleteObsoleteGames')
            ->once()
            ->with(Mockery::on(function ($argument) {
                return isset($argument['data']) && $argument['data'][0]['id'] === '123';
            }));

        $dbClientMock
            ->shouldReceive('getGameData')
            ->andReturn(collect(['gameData']));

        $dbClientMock
            ->shouldReceive('fetchGameById')
            ->with('123')
            ->andReturn(null);

        $dbClientMock
            ->shouldReceive('insertGame')
            ->with(['id' => '123', 'name' => 'Game 1'])
            ->once();

        $dbClientMock
            ->shouldReceive('updateGame')
            ->never();

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with('https://api.twitch.tv/helix/videos?game_id=123&sort=views&first=40', 'token')
            ->once()
            ->andReturn(['response' => json_encode(['data' => []]), 'status' => 200]);

        $dataManager = new TopsOfTheTopsDataManager($dbClientMock, $apiClientMock, $twitchTokenServiceMock);

        $result = $dataManager->updateExistingGamesData(3600);

        $this->assertNotEmpty($result);
        $this->assertEquals(collect(['gameData']), $result);
    }



}