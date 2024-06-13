<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Exception;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetTimelineTest extends TestCase
{
    private $mockApiClient;
    private $mockDBClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApiClient = Mockery::mock(ApiClient::class);
        $this->mockDBClient = Mockery::mock(DBClient::class);

        $this->app->instance(ApiClient::class, $this->mockApiClient);
        $this->app->instance(DBClient::class, $this->mockDBClient);
    }

    /**
     * @test
     */
    public function testGetTimelineSuccess()
    {
        $timelineData = [
            [
                'user_id' => '123',
                'user_name' => 'StreamerOne',
                'title' => 'Stream Title 1',
                'view_count' => 100,
                'created_at' => '2023-06-01T12:00:00Z'
            ],
            [
                'user_id' => '124',
                'user_name' => 'StreamerTwo',
                'title' => 'Stream Title 2',
                'view_count' => 200,
                'created_at' => '2023-06-01T13:00:00Z'
            ]
        ];

        $this->mockDBClient
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('token');

        $this->mockDBClient
            ->shouldReceive('getFollowedStreamers')
            ->once()
            ->andReturn(['123', '124']);

        $this->mockApiClient
            ->shouldReceive('makeCurlCall')
            ->twice()
            ->andReturn(
                [
                    'status' => Response::HTTP_OK,
                    'response' => json_encode(['data' => [$timelineData[0]]])
                ],
                [
                    'status' => Response::HTTP_OK,
                    'response' => json_encode(['data' => [$timelineData[1]]])
                ]
            );

        $response = $this->getJson('/analytics/timeline?userId=123');

        $responseData = json_decode($response->getContent(), true);

        $this->assertSame($timelineData, $responseData);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
