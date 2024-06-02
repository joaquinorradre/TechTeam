<?php

namespace Tests\Unit\TopOfTheTops;

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

    /**
     * @test
     * @throws Exception
     */
    public function updates_games_data_when_no_games_exist()
    {
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

        $result = $this->service->execute($since);

        $this->assertEquals($expectedData, $result);
    }

    /**
     * @test
     * @throws Exception
     */
    public function updates_existing_games_data_when_games_exist()
    {
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

        $result = $this->service->execute($since);

        $this->assertEquals($expectedData, $result);
    }

    /**
     * @test
     */
    public function throws_exception_when_update_games_data_fails()
    {
        $since = 600;
        $this->topsOfTheTopsDataManager
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn(new Collection([]));
        $this->topsOfTheTopsDataManager
            ->shouldReceive('updateGamesData')
            ->once()
            ->andThrow(new Exception('Error updating game data'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error updating game data');

        $this->service->execute($since);
    }

    /**
     * @test
     */
    public function throws_exception_when_update_existing_games_data_fails()
    {
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error updating existing game data');

        $this->service->execute($since);
    }
}