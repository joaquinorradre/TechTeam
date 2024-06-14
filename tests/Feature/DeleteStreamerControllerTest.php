<?php

namespace Tests\Feature;

use App\Http\Controllers\DeleteStreamerController;
use App\Http\Clients\DBClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteStreamerControllerTest extends TestCase
{
    /**
     * @test
     */
    public function unfollowStreamer()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock
            ->shouldReceive('deleteStreamerFromDatabase')
            ->once()
            ->with('user123', 'streamer123')
            ->andReturn(1);
        $controller = new DeleteStreamerController($dbClientMock);
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->status());
        $this->assertSame('Has dejado de seguir a streamer123', $response->getData()->message);
    }

    /**
     * @test
     */
    public function unfollowStreamerServerError()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock
            ->shouldReceive('deleteStreamerFromDatabase')
            ->once()
            ->with('user123', 'streamer123')
            ->andThrow(new \Exception('Error del servidor al dejar de seguir al streamer', Response::HTTP_INTERNAL_SERVER_ERROR));
        $controller = new DeleteStreamerController($dbClientMock);
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->status());
        $this->assertSame('Error del servidor al dejar de seguir al streamer', $response->getData()->message);
    }

    /**
     * @test
     */
    public function unfollowStreamerNotFound()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock
            ->shouldReceive('deleteStreamerFromDatabase')
            ->once()
            ->with('user123', 'streamer123')
            ->andThrow(new \Exception('El usuario user123 o el streamer streamer123 especificado no existe en la API', Response::HTTP_NOT_FOUND));
        $controller = new DeleteStreamerController($dbClientMock);
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->status());
        $this->assertSame('El usuario user123 o el streamer streamer123 especificado no existe en la API', $response->getData()->message);
    }
}