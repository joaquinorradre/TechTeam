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

class PostStreamerControllerTest extends TestCase
{
    /**
     * @test
     */
    public function followStreamer()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode(['data' => [['id' => '123']]]), 'status' => 200]);
        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock
            ->shouldReceive('addStreamerToDatabase')
            ->once()
            ->with('user123', 'streamer123');
        $streamerExistManagerMock = Mockery::mock(StreamerExistManager::class);
        $streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andReturn(true); // Simulate streamer exists
        $postStreamerService = new PostStreamerService($streamerExistManagerMock, $dbClientMock);
        $controller = new PostStreamerController($postStreamerService);
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->status());
        $this->assertSame('Ahora sigues a streamer123', $response->getData()->message);
    }
}