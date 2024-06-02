<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\DeleteStreamerController;
use App\Services\DeleteStreamerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class DeleteStreamerControllerTest extends TestCase
{

    /**
     * @test
     */
    public function invokeMethodWhenServiceReturnsTrue()
    {
        $service = Mockery::mock(DeleteStreamerService::class);
        $service
            ->shouldReceive('execute')
            ->once()
            ->with('user123', 'streamer123')
            ->andReturn(true);
        $controller = new DeleteStreamerController($service);
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->status());
        $this->assertSame('Has dejado de seguir a streamer123', $response->getData()->message);
    }

    /**
     * @test
     */
    public function invokeMethodWhenServiceReturnsFalse()
    {
        $service = Mockery::mock(DeleteStreamerService::class);
        $service
            ->shouldReceive('execute')
            ->once()
            ->with('user123', 'streamer123')
            ->andReturn(false);
        $controller = new DeleteStreamerController($service);
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->status());
        $this->assertSame('No has podido deja de seguir a streamer123', $response->getData()->message);
    }
}
