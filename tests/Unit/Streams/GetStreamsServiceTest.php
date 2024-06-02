<?php

namespace Tests\Unit\Streams;

use App\Services\GetStreamsService;
use App\Services\StreamsDataManager;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetStreamsServiceTest extends TestCase
{
    /**
     * @test
     */
    public function givenAValidResponseFromTwitchDoExecuteMethod()
    {
        $streamsDataManagerMock = Mockery::mock(StreamsDataManager::class);
        $getStreamsService = new GetStreamsService($streamsDataManagerMock);

        $streamsDataManagerMock
            ->shouldReceive('getStreams')
            ->once()
            ->andReturn(json_encode(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]));

        $result = $getStreamsService->execute();

        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals('Stream 1', $result[0]['title']);
        $this->assertEquals('User 1', $result[0]['user_name']);
    }

    /**
     * @test
     */
    public function givenANullResponseFromTwitchDoExecuteMethod()
    {
        $streamsDataManagerMock = Mockery::mock(StreamsDataManager::class);
        $getStreamsService = new GetStreamsService($streamsDataManagerMock);

        $streamsDataManagerMock
            ->shouldReceive('getStreams')
            ->once()
            ->andReturn('');

        $result = $getStreamsService->execute();

        $this->assertIsArray($result);
        $this->assertEquals(0, count($result));
    }
}
