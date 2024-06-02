<?php

namespace Tests\Unit\TopsOfTheTops;

use App\Services\GetTopOfTheTopsService;
use App\Services\TopsOfTheTopsDataManager;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetTopOfTheTopsServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function updatesAndReturnsWhenNoGames()
    {
        $since = 600;
        $topsDataManagerMock = Mockery::mock(TopsOfTheTopsDataManager::class);
        $gamesData = [
            ['id' => '123', 'name' => 'Game 1'],
            ['id' => '456', 'name' => 'Game 2'],
            ['id' => '789', 'name' => 'Game 3'],
        ];
        $topsDataManagerMock
            ->shouldReceive('fetchGames')
            ->andReturn(collect([]));

        $topsDataManagerMock
            ->shouldReceive('updateGamesData')
            ->andReturn($gamesData);
        $service = new GetTopOfTheTopsService($topsDataManagerMock);

        $result = $service->execute($since);

        $this->assertEquals($gamesData, $result);
    }

    /**
     * @test
     */
    public function updatesExistingWhenGamesExist()
    {
        $since = 600;
        $topsDataManagerMock = Mockery::mock(TopsOfTheTopsDataManager::class);
        $gamesData = [
            ['id' => '123', 'name' => 'Game 1'],
            ['id' => '456', 'name' => 'Game 2'],
            ['id' => '789', 'name' => 'Game 3'],
        ];
        $topsDataManagerMock
            ->shouldReceive('fetchGames')
            ->andReturn(collect($gamesData));
        $topsDataManagerMock
            ->shouldReceive('updateExistingGamesData')
            ->with($since)
            ->andReturn($gamesData);
        $service = new GetTopOfTheTopsService($topsDataManagerMock);

        $result = $service->execute($since);

        $this->assertEquals($gamesData, $result);
    }
}
