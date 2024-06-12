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
    private $streamerExistManagerMock;
    private $dbClientMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->streamerExistManagerMock = Mockery::mock(StreamerExistManager::class);
        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->service = new PostStreamerService($this->streamerExistManagerMock, $this->dbClientMock);
    }

    /**
     * @test
     * @throws Exception
     */
    public function when_streamer_exists_should_add_streamer_to_database()
    {
        $this->streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andReturn(true);
        $this->dbClientMock
            ->shouldReceive('addStreamerToDatabase')
            ->once()
            ->with('user123', 'streamer123');

        $this->service->execute('user123', 'streamer123');

        $this->addToAssertionCount(1);
    }

    /**
     * @test
     * @throws Exception
     */
    public function when_streamer_does_not_exist_should_not_add_streamer_to_database()
    {
        $this->streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andReturn(false);
        $this->dbClientMock
            ->shouldNotReceive('addStreamerToDatabase');

        $this->service->execute('user123', 'streamer123');

        $this->addToAssertionCount(1);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
