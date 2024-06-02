<?php

namespace Tests\Unit\Services;

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
     */
    public function executeMethodWhenStreamerExists()
    {
        $streamerExistManager = Mockery::mock(StreamerExistManager::class);
        $streamerExistManager
            ->shouldReceive('getStreamer')
            ->with('streamer')
            ->once()
            ->andReturn(true);
        $dbClient = Mockery::mock(DBClient::class);
        $dbClient
            ->shouldReceive('addStreamerToDatabase')
            ->with('user', 'streamer')
            ->once();
        $service = new PostStreamerService($streamerExistManager, $dbClient);

        $result = $service->execute('user', 'streamer');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function executeMethodWhenStreamerDoesNotExist()
    {
        $streamerExistManager = Mockery::mock(StreamerExistManager::class);
        $streamerExistManager
            ->shouldReceive('getStreamer')
            ->with('streamer')
            ->once()
            ->andReturn(false);
        $dbClient = Mockery::mock(DBClient::class);
        $dbClient
            ->shouldNotReceive('addStreamerToDatabase');

        $service = new PostStreamerService($streamerExistManager, $dbClient);
        $result = $service->execute('user', 'streamer');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testExecuteMethodThrowsException()
    {
        $streamerExistManager = Mockery::mock(StreamerExistManager::class);
        $streamerExistManager
            ->shouldReceive('getStreamer')
            ->with('streamer')
            ->once()
            ->andThrow(new Exception('Error del servidor al seguir al streamer', 500));
        $dbClient = Mockery::mock(DBClient::class);
        $service = new PostStreamerService($streamerExistManager, $dbClient);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error del servidor al seguir al streamer');
        $this->expectExceptionCode(500);

        $service->execute('user', 'streamer');
    }

}
