<?php

namespace Tests\Unit\Streams;

use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetStreamsServiceTest extends TestCase
{
    public function testExecuteMethodWithValidResponseFromTwitch()
    {
        $streamsDataManagerMock = Mockery::mock(StreamsDataManager::class);

        $streamsDataManagerMock
            ->shouldReceive('getStreams')
            ->once()
            ->andReturn(json_encode(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]));

        $getStreamsService = new GetStreamsService($streamsDataManagerMock);

        $result = $getStreamsService->execute();

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals('Stream 1', $result[0]['title']);
        $this->assertEquals('User 1', $result[0]['user_name']);
    }

    public function testExecuteMethodWithNullResponseFromTwitch()
    {
        $streamsDataManagerMock = Mockery::mock(StreamsDataManager::class);

        $streamsDataManagerMock
            ->shouldReceive('getStreams')
            ->once()
            ->andReturn('');

        $getStreamsService = new GetStreamsService($streamsDataManagerMock);

        $result = $getStreamsService->execute();

        $this->assertIsArray($result);
        $this->assertEquals(0, count($result));
    }

}
