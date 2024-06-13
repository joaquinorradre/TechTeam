<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Serializers\UserDataSerializer;
use Mockery;
use Tests\TestCase;

class GetUsersTest extends TestCase
{
    protected $apiClientMock;
    protected $dbClientMock;
    protected $userDataSerializerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->userDataSerializerMock = Mockery::mock(UserDataSerializer::class);

        $this->app->instance(ApiClient::class, $this->apiClientMock);
        $this->app->instance(DBClient::class, $this->dbClientMock);
        $this->app->instance(UserDataSerializer::class, $this->userDataSerializerMock);
    }

    /**
     * @test
     */
    public function getUsers()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('token');

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with('https://api.twitch.tv/helix/users?id=123', 'token')
            ->andReturn([
                'response' => json_encode(['data' => [['id' => '123', 'login' => 'login']]]),
                'status' => 200,
            ]);

        $this->userDataSerializerMock
            ->shouldReceive('serialize')
            ->once()
            ->with([['id' => '123', 'login' => 'login']])
            ->andReturn([['id' => '123', 'login' => 'login']]);

        $response = $this->getJson('/analytics/streamers?id=123');

        $response->assertStatus(200);
        $response->assertJson([['id' => '123', 'login' => 'login']]);
    }

    /**
     * @test
     */
    public function getUserServerError()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with('https://api.twitch.tv/helix/users?id=789', 'token')
            ->andReturn([
                'response' => json_encode(['error' => 'Internal Server Error']),
                'status' => 500,
            ]);

        $response = $this->getJson('/analytics/streamers?id=789');

        $response->assertStatus(503);
        $response->assertJson([
            'error' => 'Service Unavailable',
            'message' => 'No se pueden devolver usuarios en este momento, inténtalo más tarde'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
