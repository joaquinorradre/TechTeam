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
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
    }

    /**
     * @test
     */
    public function fetch_games_returns_collection()
    {
        $this->dbClientMock
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn(collect(['game1', 'game2', 'game3']));
        $dataManager = new TopsOfTheTopsDataManager($this->dbClientMock, $this->apiClientMock, $this->twitchTokenServiceMock);

        $result = $dataManager->fetchGames();

        $this->assertCount(3, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function update_games_data_updates_and_returns_game_data()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with('https://api.twitch.tv/helix/games/top?first=3', 'token')
            ->once()
            ->andReturn(['response' => json_encode(['data' => [['id' => '123', 'name' => 'Game 1']]]), 'status' => 200]);
        $this->dbClientMock
            ->shouldReceive('getGameData')
            ->andReturn(collect(['gameData']));
        $this->dataManagerMock = Mockery::mock(TopsOfTheTopsDataManager::class, [$this->dbClientMock, $this->apiClientMock, $this->twitchTokenServiceMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->dataManagerMock
            ->shouldReceive('insertGames')
            ->once();
        $this->dataManagerMock
            ->shouldReceive('fetchAndInsertVideos')
            ->once();

        $result = $this->dataManagerMock->updateGamesData();

        $this->assertNotEmpty($result);
        $this->assertEquals(collect(['gameData']), $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function update_existing_games_data_updates_and_returns_game_data()
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

}