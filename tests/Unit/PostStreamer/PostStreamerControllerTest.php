<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\PostStreamerController;
use App\Services\PostStreamerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class PostStreamerControllerTest extends TestCase
{

    /**
     * @test
     */
    public function invokeMethodWhenServiceReturnsTrue()
    {
        $request = new Request(['userId' => 'user', 'streamerId' => 'streamer']);
        $postStreamerService = Mockery::mock(PostStreamerService::class);
        $postStreamerService
            ->shouldReceive('execute')
            ->with('user', 'streamer')
            ->once()
            ->andReturn(true);
        $controller = new PostStreamerController($postStreamerService);

        $response = $controller->__invoke($request);

        $expectedResponse = new JsonResponse(['message' => 'Ahora sigues a streamer'], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     */
    public function invokeMethodWhenServiceReturnsFalse()
    {
        $request = new Request(['userId' => 'user', 'streamerId' => 'streamer']);
        $postStreamerService = Mockery::mock(PostStreamerService::class);
        $postStreamerService
            ->shouldReceive('execute')
            ->with('user', 'streamer')
            ->once()
            ->andReturn(false);
        $controller = new PostStreamerController($postStreamerService);

        $response = $controller->__invoke($request);

        $expectedResponse = new JsonResponse(['message' => 'No sigues a streamer'], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($expectedResponse, $response);
    }
}
