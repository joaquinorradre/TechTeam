<?php

namespace Tests\Unit\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TopsOfTheTopsDataManager;
use App\Services\TwitchTokenService;
use Exception;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class TopsOfTheTopsDataManagerTest extends TestCase
{
    /** @var MockInterface */
    private $dbClient;

    /** @var MockInterface */
    private $apiClient;

    /** @var MockInterface */
    private $twitchTokenService;

    /** @var TopsOfTheTopsDataManager */
    private $topsOfTheTopsDataManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClient = Mockery::mock(DBClient::class);
        $this->apiClient = Mockery::mock(ApiClient::class);
        $this->twitchTokenService = Mockery::mock(TwitchTokenService::class);

        $this->topsOfTheTopsDataManager = new TopsOfTheTopsDataManager(
            $this->dbClient,
            $this->apiClient,
            $this->twitchTokenService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function fetchGamesReturnsGamesCollection()
    {
        // Arrange
        $expectedGames = new Collection([
            ['id' => '1', 'name' => 'Game 1'],
            ['id' => '2', 'name' => 'Game 2']
        ]);
        $this->dbClient
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn($expectedGames);

        // Act
        $result = $this->topsOfTheTopsDataManager->fetchGames();

        // Assert
        $this->assertEquals($expectedGames, $result);
    }

    /** @test */
    public function updateGamesDataInsertsGamesAndVideosWhenNoErrors()
    {
        // Arrange
        $accessToken = 'fake_token';
        $gamesResponse = [
            'data' => [
                ['id' => '1', 'name' => 'Game 1'],
                ['id' => '2', 'name' => 'Game 2'],
                ['id' => '3', 'name' => 'Game 3']
            ]
        ];

        $gamesResult = [
            'response' => json_encode($gamesResponse),
            'status' => 200
        ];

        $this->twitchTokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($accessToken);

        $this->apiClient
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with(TopsOfTheTopsDataManager::GAMES_TWITCH_URL, $accessToken)
            ->andReturn($gamesResult);

        $this->dbClient
            ->shouldReceive('getGameData')
            ->once()
            ->andReturn($gamesResponse);

        // Act
        $result = $this->topsOfTheTopsDataManager->updateGamesData();

        // Assert
        $this->assertEquals($gamesResponse, $result);
    }

    /** @test */
    public function updateExistingGamesDataUpdatesGamesAndVideosWhenNoErrors()
    {
        // Arrange
        $since = 600;
        $accessToken = 'fake_token';
        $gamesResponse = [
            'data' => [
                ['id' => '1', 'name' => 'Game 1'],
                ['id' => '2', 'name' => 'Game 2'],
                ['id' => '3', 'name' => 'Game 3']
            ]
        ];

        $gamesResult = [
            'response' => json_encode($gamesResponse),
            'status' => 200
        ];

        $this->twitchTokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($accessToken);

        $this->apiClient
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with(TopsOfTheTopsDataManager::GAMES_TWITCH_URL, $accessToken)
            ->andReturn($gamesResult);

        $this->dbClient
            ->shouldReceive('deleteObsoleteGames')
            ->once()
            ->with($gamesResponse);

        $this->dbClient
            ->shouldReceive('getGameData')
            ->once()
            ->andReturn($gamesResponse);

        // Act
        $result = $this->topsOfTheTopsDataManager->updateExistingGamesData($since);

        // Assert
        $this->assertEquals($gamesResponse, $result);
    }

    /** @test */
    public function updateGamesDataThrowsExceptionWhenTokenServiceFails()
    {
        // Arrange
        $this->twitchTokenService
            ->shouldReceive('getToken')
            ->once()
            ->andThrow(new Exception('Token error'));

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token error');

        // Act
        $this->topsOfTheTopsDataManager->updateGamesData();
    }

    /** @test */
    public function updateExistingGamesDataThrowsExceptionWhenApiCallFails()
    {
        // Arrange
        $since = 600;
        $accessToken = 'fake_token';
        $this->twitchTokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($accessToken);

        $this->apiClient
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with(TopsOfTheTopsDataManager::GAMES_TWITCH_URL, $accessToken)
            ->andThrow(new Exception('API error'));

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API error');

        // Act
        $this->topsOfTheTopsDataManager->updateExistingGamesData($since);
    }
}
