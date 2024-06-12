<?php

namespace Tests\Unit;

use App\Services\GetTimelineService;
use App\Services\TimelineDataManager;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetTimelineServiceTest extends TestCase
{
    protected $timelineDataManagerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timelineDataManagerMock = Mockery::mock(TimelineDataManager::class);
        $this->service = new GetTimelineService($this->timelineDataManagerMock);
    }

    /**
     * @test
     */
    public function when_executing_with_success_timeline_data_should_be_returned()
    {
        $this->timelineDataManagerMock
            ->shouldReceive('getTimeline')
            ->with('1')
            ->andReturn(['timeline' => 'data']);

        $result = $this->service->execute('1');

        $this->assertEquals(['timeline' => 'data'], $result);
    }

    /**
     * @test
     */
    public function when_executing_with_exception_should_throw_exception()
    {
        $this->timelineDataManagerMock
            ->shouldReceive('getTimeline')
            ->with('1')
            ->andThrow(new Exception('Error message'));

        $this->service->execute('1');

        $this->expectException(Exception::class);
    }
}
