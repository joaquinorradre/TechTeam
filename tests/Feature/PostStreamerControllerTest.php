<?php

namespace Tests\Feature;

use App\Http\Controllers\PostStreamerController;
use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\PostStreamerService;
use App\Services\StreamerExistManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

class PostStreamerControllerTest extends TestCase
{
    protected $apiClientMock;
    protected $dbClientMock;
    protected $streamerExistManagerMock;
    protected $postStreamerService;
    protected $controller;
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->streamerExistManagerMock = Mockery::mock(StreamerExistManager::class);

        $this->postStreamerService = new PostStreamerService($this->streamerExistManagerMock, $this->dbClientMock);
        $this->controller = new PostStreamerController($this->postStreamerService);
        $this->request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);
    }

    /**
     * @test
     */
    public function postStreamer()
    {
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode(['data' => [['id' => '123']]]), 'status' => 200]);

        $this->dbClientMock
            ->shouldReceive('addStreamerToDatabase')
            ->once()
            ->with('user123', 'streamer123');

        $this->streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andReturn(true);

        $response = $this->controller->__invoke($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->status());
        $this->assertSame('Ahora sigues a streamer123', $response->getData()->message);
    }

    /**
     * @test
     */
    public function postStreamerServerError()
    {
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => 'Internal server error', 'status' => Response::HTTP_INTERNAL_SERVER_ERROR]);

        $this->streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andThrow(new \Exception('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR));

        $response = $this->controller->__invoke($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->status());
        $this->assertSame('Internal server error', $response->getData()->message);
    }

    /**
     * @test
     */
    public function postStreamerNotFound()
    {
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => 'Streamer not found', 'status' => Response::HTTP_NOT_FOUND]);

        $this->streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andThrow(new \Exception('Streamer not found', Response::HTTP_NOT_FOUND));

        $response = $this->controller->__invoke($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->status());
        $this->assertSame('Streamer not found', $response->getData()->message);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
