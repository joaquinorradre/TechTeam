<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetTopOfTheTopsTest extends TestCase
{
    protected $apiClientMock;
    protected $dbClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->dbClientMock = Mockery::mock(DBClient::class);

        $this->app->instance(ApiClient::class, $this->apiClientMock);
        $this->app->instance(DBClient::class, $this->dbClientMock);
    }

    /**
     * @test
     */
    public function getTopOfTheTops()
    {
        $gamesData = [
            'data' => [
                ['id' => '123', 'name' => 'Game 1'],
                ['id' => '456', 'name' => 'Game 2'],
                ['id' => '789', 'name' => 'Game 3'],
            ]
        ];

        $videosData = [
            'data' => [
                ['id' => 'v123', 'user_id' => 'u123', 'user_name' => 'User 1', 'title' => 'Video 1', 'created_at' => '2024-06-10T12:34:56Z', 'view_count' => 100, 'duration' => '2h30m', 'game_id' => '123'],
                ['id' => 'v456', 'user_id' => 'u456', 'user_name' => 'User 2', 'title' => 'Video 2', 'created_at' => '2024-06-11T12:34:56Z', 'view_count' => 200, 'duration' => '1h20m', 'game_id' => '456'],
                ['id' => 'v789', 'user_id' => 'u789', 'user_name' => 'User 3', 'title' => 'Video 3', 'created_at' => '2024-06-12T12:34:56Z', 'view_count' => 300, 'duration' => '3h45m', 'game_id' => '789']
            ]
        ];

        $this->dbClientMock
            ->shouldReceive('fetchGames')
            ->once()
            ->andReturn(collect([]));

        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->andReturn('token');

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->andReturn([
                'response' => json_encode($gamesData),
                'status' => Response::HTTP_OK
            ]);

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->withArgs(function ($url, $token) use ($gamesData) {
                foreach ($gamesData['data'] as $game) {
                    if ($url === "https://api.twitch.tv/helix/videos?game_id={$game['id']}&sort=views&first=40" && $token === 'token') {
                        return true;
                    }
                }
                return false;
            })
            ->times(count($gamesData['data']))
            ->andReturn([
                'response' => json_encode($videosData),
                'status' => Response::HTTP_OK
            ]);

        $this->dbClientMock
            ->shouldReceive('getGameData')
            ->once()
            ->andReturn($gamesData['data']);

        $response = $this->getJson('/analytics/topsofthetops?since=600');

        $this->assertEquals(Response::HTTP_OK, $response->status());
        $this->assertJsonStringEqualsJsonString(
            json_encode($gamesData['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $response->getContent()
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
