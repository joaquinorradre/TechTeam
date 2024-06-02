<?php

namespace Tests\Unit\PostStreamer;

use App\Http\Clients\DBClient;
use App\Services\PostStreamerService;
use App\Services\StreamerExistManager;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

class PostStreamerServiceTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function executeAddsStreamerToDatabaseWhenStreamerExists()
    {
        $streamerExistManagerMock = Mockery::mock(StreamerExistManager::class);
        $streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andReturn(true);
        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock
            ->shouldReceive('addStreamerToDatabase')
            ->once()
            ->with('user123', 'streamer123');
        $service = new PostStreamerService($streamerExistManagerMock, $dbClientMock);

        $service->execute('user123', 'streamer123');

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     * @throws Exception
     */
    public function executeDoesNotAddStreamerToDatabaseWhenStreamerDoesNotExist()
    {
        $streamerExistManagerMock = Mockery::mock(StreamerExistManager::class);
        $streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andReturn(false);
        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock
            ->shouldNotReceive('addStreamerToDatabase');
        $service = new PostStreamerService($streamerExistManagerMock, $dbClientMock);

        $service->execute('user123', 'streamer123');

        $this->addToAssertionCount(1);
    }
}