<?php

namespace Tests\Unit;

use App\Services\GetTimelineService;
use App\Services\TimelineDataManager;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetTimelineServiceTest extends TestCase
{

    /**
     * @test
     */
    public function ExecuteSuccess()
    {
        $timelineDataManagerMock = Mockery::mock(TimelineDataManager::class);
        $timelineDataManagerMock->shouldReceive('getTimeline')->with('1')->andReturn(['timeline' => 'data']);

        $service = new GetTimelineService($timelineDataManagerMock);
        $result = $service->execute('1');

        $this->assertEquals(['timeline' => 'data'], $result);
    }

    /**
     * @test
     */
    public function ExecuteException()
    {
        $this->expectException(Exception::class);

        $timelineDataManagerMock = Mockery::mock(TimelineDataManager::class);
        $timelineDataManagerMock->shouldReceive('getTimeline')->with('1')->andThrow(new Exception('Error message'));

        $service = new GetTimelineService($timelineDataManagerMock);
        $service->execute('1');
    }
}