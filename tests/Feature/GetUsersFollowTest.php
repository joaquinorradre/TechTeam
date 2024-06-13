<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Illuminate\Http\Response;
use Tests\TestCase;

class GetUsersFollowTest extends TestCase
{
    protected $apiClientMock;
    protected $dbClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClientMock = $this->mock(DBClient::class);
        $this->apiClientMock = $this->mock(ApiClient::class);

        $this->app->instance(ApiClient::class, $this->apiClientMock);
        $this->app->instance(DBClient::class, $this->dbClientMock);
    }

    /**
     * @test
     */
    public function getUsersFollowTestSuccess()
    {
        $expectedData = [
            'user1' => ['login1', 'login2'],
            'user2' => ['login3'],
        ];
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->andReturn('token');
        $this->dbClientMock
            ->shouldReceive('getUsersWithFollowedStreamers')
            ->once()
            ->andReturn([
                (object) ['username' => 'user1', 'streamerId' => 'streamer1'],
                (object) ['username' => 'user1', 'streamerId' => 'streamer2'],
                (object) ['username' => 'user2', 'streamerId' => 'streamer3'],
            ]);
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->times(3)
            ->andReturn(
                ['status' => Response::HTTP_OK, 'response' => json_encode(['data' => [['login' => 'login1']]])],
                ['status' => Response::HTTP_OK, 'response' => json_encode(['data' => [['login' => 'login2']]])],
                ['status' => Response::HTTP_OK, 'response' => json_encode(['data' => [['login' => 'login3']]])]
            );

        $response = $this->getJson('/analytics/users');

        $response->assertStatus(200);
        $responseData = $response->json();
        $transformedData = [];
        foreach ($responseData as $item) {
            $transformedData[$item['username']] = $item['followedStreamers'];
        }
        $this->assertEquals($expectedData, $transformedData);
    }
}
