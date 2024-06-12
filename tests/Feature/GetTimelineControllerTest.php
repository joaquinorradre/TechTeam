<?php

namespace Tests\Feature;

use App\Http\Controllers\GetTimelineController;
use App\Http\Requests\GetTimelineRequest;
use App\Services\GetTimelineService;
use Illuminate\Http\JsonResponse;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetTimelineControllerTest extends TestCase
{
    protected $timelineServiceMock;
    protected $controller;
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timelineServiceMock = Mockery::mock(GetTimelineService::class);
        $this->controller = new GetTimelineController($this->timelineServiceMock);
        $this->request = new GetTimelineRequest(['userId' => 'user123']);
    }

    /**
     * @test
     */
    public function getTimelineSuccess()
    {
        $timelineData = [
            [
                'streamerId' => '123',
                'userName' => 'StreamerOne',
                'title' => 'Stream Title 1',
                'gameName' => 'Game One',
                'viewerCount' => 100,
                'startedAt' => '2023-06-01T12:00:00Z'
            ],
            [
                'streamerId' => '124',
                'userName' => 'StreamerTwo',
                'title' => 'Stream Title 2',
                'gameName' => 'Game Two',
                'viewerCount' => 200,
                'startedAt' => '2023-06-01T13:00:00Z'
            ]
        ];
        $this->timelineServiceMock
            ->shouldReceive('execute')
            ->once()
            ->with('user123')
            ->andReturn($timelineData);

        $response = $this->controller->__invoke($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());
        $this->assertSame($timelineData, $response->getData(true));
    }

    /**
     * @test
     */
    public function getTimelineNotFound()
    {
        $this->timelineServiceMock
            ->shouldReceive('execute')
            ->once()
            ->with('user123')
            ->andThrow(new \Exception("El usuario especificado user123 no sigue a ningún streamer", Response::HTTP_INTERNAL_SERVER_ERROR));

        $response = $this->controller->__invoke($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->status());
        $this->assertSame(['error' => "El usuario especificado user123 no sigue a ningún streamer"], $response->getData(true));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getTimelineError()
    {
        $this->timelineServiceMock
            ->shouldReceive('execute')
            ->once()
            ->with('user123')
            ->andThrow(new \Exception("Error inesperado", Response::HTTP_INTERNAL_SERVER_ERROR));

        $response = $this->controller->__invoke($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->status());
        $this->assertSame(['error' => 'Error inesperado'], $response->getData(true));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
