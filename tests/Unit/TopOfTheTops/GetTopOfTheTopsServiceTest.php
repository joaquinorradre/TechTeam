<?php

namespace Tests\Unit\Services;

use App\Services\GetTopOfTheTopsService;
use App\Services\TopsOfTheTopsDataManager;
use Exception;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetTopOfTheTopsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->topsOfTheTopsDataManager = Mockery::mock(TopsOfTheTopsDataManager::class);
        $this->service = new GetTopOfTheTopsService($this->topsOfTheTopsDataManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function updatesGamesDataWhenNoGamesExist()
    {
        // Arrange
        $since = 600;
        $this->topsOfTheTopsDataManager
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn(new Collection([]));

        $expectedData = [
            'data' => [
                ['id' => '1', 'name' => 'Game 1'],
                ['id' => '2', 'name' => 'Game 2'],
                ['id' => '3', 'name' => 'Game 3']
            ]
        ];

        $this->topsOfTheTopsDataManager
            ->shouldReceive('updateGamesData')
            ->once()
            ->andReturn($expectedData);

        // Act
        $result = $this->service->execute($since);

        // Assert
        $this->assertEquals($expectedData, $result);
    }

    /** @test */
    public function updatesExistingGamesDataWhenGamesExist()
    {
        // Arrange
        $since = 600;
        $this->topsOfTheTopsDataManager
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn(new Collection([
                ['id' => '1', 'name' => 'Game 1']
            ]));

        $expectedData = [
            'data' => [
                ['id' => '1', 'name' => 'Game 1'],
                ['id' => '2', 'name' => 'Game 2'],
                ['id' => '3', 'name' => 'Game 3']
            ]
        ];

        $this->topsOfTheTopsDataManager
            ->shouldReceive('updateExistingGamesData')
            ->once()
            ->with($since)
            ->andReturn($expectedData);

        // Act
        $result = $this->service->execute($since);

        // Assert
        $this->assertEquals($expectedData, $result);
    }

    /** @test */
    public function throwsExceptionWhenUpdateGamesDataFails()
    {
        // Arrange
        $since = 600;
        $this->topsOfTheTopsDataManager
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn(new Collection([]));

        $this->topsOfTheTopsDataManager
            ->shouldReceive('updateGamesData')
            ->once()
            ->andThrow(new Exception('Error updating game data'));

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error updating game data');

        // Act
        $this->service->execute($since);
    }

    /** @test */
    public function throwsExceptionWhenUpdateExistingGamesDataFails()
    {
        // Arrange
        $since = 600;
        $this->topsOfTheTopsDataManager
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn(new Collection([
                ['id' => '1', 'name' => 'Game 1']
            ]));

        $this->topsOfTheTopsDataManager
            ->shouldReceive('updateExistingGamesData')
            ->once()
            ->with($since)
            ->andThrow(new Exception('Error updating existing game data'));

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error updating existing game data');

        // Act
        $this->service->execute($since);
    }
}
