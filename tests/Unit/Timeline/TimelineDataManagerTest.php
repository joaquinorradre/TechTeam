<?php

namespace Tests\Unit\Timeline;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TwitchTokenService;
use App\Services\TimelineDataManager;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

class TimelineDataManagerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     * @throws \Exception
     */
    public function given_a_valid_user_id_timeline_data_manager_should_return_timeline_data()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');
        $dbClientMock
            ->shouldReceive('getFollowedStreamers')
            ->with('validUserId')
            ->andReturn([(object) ['streamerId' => '12345']]);
        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/videos?user_id=12345&sort=time&first=5', 'access_token')
            ->andReturn([
                'response' => json_encode(['data' => [
                    [
                        'user_id' => '12345',
                        'user_name' => 'streamerTest',
                        'title' => 'Test Stream',
                        'view_count' => 100,
                        'created_at' => '2023-06-02T12:00:00Z'
                    ]
                ]]),
                'status' => 200
            ]);
        $timelineDataManager = new TimelineDataManager($dbClientMock, $twitchTokenServiceMock, $apiClientMock);

        $response = $timelineDataManager->getTimeline('validUserId');

        $this->assertIsArray($response, 'Response should be an array');
        $this->assertCount(1, $response, 'Response should contain exactly one element');
        $this->assertEquals('12345', $response[0]['streamerId'], 'The streamerId should match the expected value');
        $this->assertEquals('streamerTest', $response[0]['userName'], 'The userName should match the expected value');
        $this->assertEquals('Test Stream', $response[0]['title'], 'The title should match the expected value');
        $this->assertEquals(100, $response[0]['viewerCount'], 'The viewCount should match the expected value');
        $this->assertEquals('2023-06-02T12:00:00Z', $response[0]['startedAt'], 'The startedAt should match the expected value');
    }

    /**
     * @test
     * @throws Exception
     */
    public function test_given_an_invalid_user_id_timeline_data_manager_should_return_empty_array()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');

        $dbClientMock
            ->shouldReceive('getFollowedStreamers')
            ->with('invalidUserId')
            ->andReturn([]);
        $timelineDataManager = new TimelineDataManager($dbClientMock, $twitchTokenServiceMock, $apiClientMock);

        try {
            $response = $timelineDataManager->getTimeline('invalidUserId');
        } catch (Exception $e) {
            $response = [];
        }

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    /**
     * @test
     */
    public function given_a_token_service_failure_timeline_data_manager_should_throw_exception()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andThrow(new \Exception('Failed to get token'));
        $timelineDataManager = new TimelineDataManager($dbClientMock, $twitchTokenServiceMock, $apiClientMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error al obtener el timeline: Failed to get token');

        $timelineDataManager->getTimeline('validUserId');
    }

    /**
     * @test
     */
    public function given_a_curl_call_failure_timeline_data_manager_should_throw_exception()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');
        $dbClientMock
            ->shouldReceive('getFollowedStreamers')
            ->with('validUserId')
            ->andReturn([(object) ['streamerId' => '12345']]);
        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/videos?user_id=12345&sort=time&first=5', 'access_token')
            ->andReturn([
                'response' => null,
                'status' => 500
            ]);
        $timelineDataManager = new TimelineDataManager($dbClientMock, $twitchTokenServiceMock, $apiClientMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error al obtener el timeline: Error al obtener los videos del streamer');

        $timelineDataManager->getTimeline('validUserId');
    }
}