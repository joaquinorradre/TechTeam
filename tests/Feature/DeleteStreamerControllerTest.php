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
    protected $dbClientMock;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->controller = new DeleteStreamerController($this->dbClientMock);
    }

    /**
     * @test
     */
    public function unfollowStreamer()
    {
        $this->dbClientMock
            ->shouldReceive('deleteStreamerFromDatabase')
            ->once()
            ->with('user123', 'streamer123')
            ->andReturn(1);
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->status());
        $this->assertSame('Has dejado de seguir a streamer123', $response->getData()->message);
    }

    /**
     * @test
     */
    public function unfollowStreamerServerError()
    {
        $this->dbClientMock
            ->shouldReceive('deleteStreamerFromDatabase')
            ->once()
            ->with('user123', 'streamer123')
            ->andThrow(new \Exception('Error del servidor al dejar de seguir al streamer', Response::HTTP_INTERNAL_SERVER_ERROR));
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->status());
        $this->assertSame('Error del servidor al dejar de seguir al streamer', $response->getData()->message);
    }

    /**
     * @test
     */
    public function unfollowStreamerNotFound()
    {
        $this->dbClientMock
            ->shouldReceive('deleteStreamerFromDatabase')
            ->once()
            ->with('user123', 'streamer123')
            ->andThrow(new \Exception('El usuario user123 o el streamer streamer123 especificado no existe en la API', Response::HTTP_NOT_FOUND));
        $request = new Request(['userId' => 'user123', 'streamerId' => 'streamer123']);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->status());
        $this->assertSame('El usuario user123 o el streamer streamer123 especificado no existe en la API', $response->getData()->message);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
