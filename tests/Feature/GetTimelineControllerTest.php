<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Http\Controllers\GetTimelineController;
use App\Http\Requests\GetTimelineRequest;
use App\Serializers\TimelineDataSerializer;
use App\Services\GetTimelineService;
use App\Services\TimelineDataManager;
use App\Services\TwitchTokenService;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetTimelineControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getTimelineControllerIntegrationTest()
    {
        $request = GetTimelineRequest::create('/timeline', 'GET', ['userId' => 'testUser']);

        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $timelineDataManagerMock = Mockery::mock(TimelineDataManager::class);
        $timelineDataSerializerMock = Mockery::mock(TimelineDataSerializer::class);
        $getTimelineServiceMock = Mockery::mock(GetTimelineService::class);

        $expectedTimeline = [
            [
                'streamerId' => '12345',
                'streamerName' => 'streamerTest',
                'title' => 'Test Stream',
                'game' => 'Test Game',
                'viewerCount' => 100,
                'startedAt' => '2023-06-02T12:00:00Z'
            ]
        ];

        $getTimelineServiceMock
            ->shouldReceive('execute')
            ->with('testUser')
            ->once()
            ->andReturn($expectedTimeline);

        $timelineDataManagerMock
            ->shouldReceive('getTimeline')
            ->with('testUser')
            ->once()
            ->andReturn($expectedTimeline);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('testToken');

        $dbClientMock
            ->shouldReceive('getFollowedStreamers')
            ->with('testUser')
            ->once()
            ->andReturn([
                (object) ['streamerId' => '12345']
            ]);

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn([
                'response' => json_encode([
                    'data' => [
                        [
                            'user_id' => '12345',
                            'user_name' => 'streamerTest',
                            'title' => 'Test Stream',
                            'game_name' => 'Test Game',
                            'viewer_count' => 100,
                            'created_at' => '2023-06-02T12:00:00Z'
                        ]
                    ]
                ]),
                'status' => 200
            ]);

        $timelineDataSerializerMock
            ->shouldReceive('serialize')
            ->once()
            ->with($expectedTimeline)
            ->andReturn($expectedTimeline);

        $getTimelineController = new GetTimelineController($getTimelineServiceMock, $timelineDataSerializerMock);

        $result = $getTimelineController->__invoke($request);

        $this->assertNotEmpty($result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals($expectedTimeline, $result->getData(true));
    }
}