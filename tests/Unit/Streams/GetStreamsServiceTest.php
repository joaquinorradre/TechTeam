<?php

namespace Tests\Unit\Streams;

use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetStreamsServiceTest extends TestCase
{
    private $streamsDataManagerMock;
    private $getStreamsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->streamsDataManagerMock = Mockery::mock(StreamsDataManager::class);
        $this->getStreamsService = new GetStreamsService($this->streamsDataManagerMock);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function given_valid_response_from_twitch_should_execute_method()
    {
        $this->streamsDataManagerMock
            ->shouldReceive('getStreams')
            ->once()
            ->andReturn(json_encode(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]));

        $result = $this->getStreamsService->execute();

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals('Stream 1', $result[0]['title']);
        $this->assertEquals('User 1', $result[0]['user_name']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function given_null_response_from_twitch_should_execute_method()
    {
        $this->streamsDataManagerMock
            ->shouldReceive('getStreams')
            ->once()
            ->andReturn('');

        $result = $this->getStreamsService->execute();

        $this->assertIsArray($result);
        $this->assertEquals(0, count($result));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
