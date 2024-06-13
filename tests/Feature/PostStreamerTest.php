<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Illuminate\Http\Response;
use Tests\TestCase;

class PostStreamerTest extends TestCase
{
    protected $dbClientMock;
    private $apiClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClientMock = $this->mock(DBClient::class);
        $this->apiClientMock = $this->mock(ApiClient::class);

        $this->app->instance(DBClient::class, $this->dbClientMock);
        $this->app->instance(ApiClient::class, $this->apiClientMock);
    }

    /**
     * @test
     */
    public function postStreamer()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->andReturn('token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn([
                'status' => 200,
                'response' => json_encode(['data' => ['id' => 2, 'username' => 'streamer123']]),
            ]);
        $this->dbClientMock
            ->shouldReceive('addStreamerToDatabase');

        $response = $this->postJson('/analytics/follow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123'
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                'message' => 'Ahora sigues a streamer123'
            ]);
    }

    /**
     * @test
     */
    public function postStreamerServerError()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->andReturn('token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn([
                'status' => 200,
                'response' => json_encode(['data' => ['id' => 2, 'username' => 'streamer123']]),
            ]);
        $this->dbClientMock
            ->shouldReceive('addStreamerToDatabase')
            ->andThrow(new \Exception('El usuario user123 especificado no existe en la BBDD', Response::HTTP_NOT_FOUND));

        $response = $this->postJson('/analytics/follow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123'
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertExactJson([
                'message' => 'El usuario user123 especificado no existe en la BBDD'
            ]);
    }


    /**
     * @test
     */
    public function postStreamerNotFound()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->andReturn('token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn([
                'status' => 404,
                'response' => json_encode(['error' => 'Streamer not found']),
            ]);

        $response = $this->postJson('/analytics/follow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123'
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertExactJson([
                'message' => 'El streamer streamer123 especificado no existe en la API'
            ]);
    }

    /**
     * @test
     */
    public function postStreamerConflict()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->andReturn('token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn([
                'status' => 200,
                'response' => json_encode(['data' => ['id' => 2, 'username' => 'streamer123']]),
            ]);
        $this->dbClientMock
            ->shouldReceive('addStreamerToDatabase')
            ->andThrow(new \Exception('El usuario ya está siguiendo al streamer', Response::HTTP_CONFLICT));

        $response = $this->postJson('/analytics/follow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123'
        ]);

        $response->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson([
                'message' => 'El usuario ya está siguiendo al streamer'
            ]);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
