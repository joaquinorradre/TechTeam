<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Tests\TestCase;
use App\Serializers\StreamsDataSerializer;

class GetStreamsTest extends TestCase
{

    private $mockStreamsDataSerializer;
    private $mockApiClient;
    private $mockDBClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockStreamsDataSerializer = $this->createMock(StreamsDataSerializer::class);
        $this->mockApiClient = $this->createMock(ApiClient::class);
        $this->mockDBClient = $this->createMock(DBClient::class);

        $this->app->instance(StreamsDataSerializer::class, $this->mockStreamsDataSerializer);
        $this->app->instance(ApiClient::class, $this->mockApiClient);
        $this->app->instance(DBClient::class, $this->mockDBClient);
    }

    /**
     * @test
     */
    public function getStreams()
    {
        $this->mockStreamsDataSerializer
            ->method('serialize')
            ->willReturn(['data' => [['title' => 'title', 'user_name' => 'Stream 1'], ['title' => 'title', 'user_name' => 'Stream 2']]]);

        $this->mockDBClient
            ->method('getTokenFromDatabase')
            ->willReturn('token');

        $this->mockApiClient
            ->method('makeCurlCall')->willReturn([
                'status' => 200,
                'response' => json_encode(['data' => [['title' => 'title', 'user_name' => 'Stream 1'], ['title' => 'title', 'user_name' => 'Stream 2']]]),
            ]);

        $response = $this->getJson('/analytics/streams');

        $response->assertStatus(200);
        $response->assertExactJson(['data' => [['title' => 'title', 'user_name' => 'Stream 1'], ['title' => 'title', 'user_name' => 'Stream 2']]]);
    }

    /**
     * @test
     */
    public function getStreamsServerError()
    {
        $this->mockDBClient
            ->method('getTokenFromDatabase')
            ->willReturn('token');

        $this->mockApiClient
            ->method('makeCurlCall')->willReturn([
                'status' => 500,
                'response' => json_encode(['error' => 'Internal Server Error']),
            ]);

        $response = $this->getJson('/analytics/streams');

        $response->assertStatus(503);
        $response->assertJson([
            'error' => 'Service Unavailable',
            'message' => 'No se pueden devolver streams en este momento, inténtalo más tarde'
        ]);
    }
}
