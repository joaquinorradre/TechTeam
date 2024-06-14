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
        $timelineServiceMock = Mockery::mock(GetTimelineService::class);
        $timelineServiceMock
            ->shouldReceive('execute')
            ->once()
            ->with('user123')
            ->andReturn($timelineData);
        $controller = new GetTimelineController($timelineServiceMock);
        $request = new GetTimelineRequest(['userId' => 'user123']);

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->status());
        $this->assertSame($timelineData, $response->getData(true));
    }

    /**
     * @test
     */
    public function getTimelineNotFound()
    {
        $timelineServiceMock = Mockery::mock(GetTimelineService::class);
        $timelineServiceMock
            ->shouldReceive('execute')
            ->once()
            ->with('user123')
            ->andThrow(new \Exception("El usuario especificado user123 no sigue a ningún streamer", Response::HTTP_INTERNAL_SERVER_ERROR));
        $controller = new GetTimelineController($timelineServiceMock);
        $request = new GetTimelineRequest(['userId' => 'user123']);

        $response = $controller->__invoke($request);

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
        $timelineServiceMock = Mockery::mock(GetTimelineService::class);
        $timelineServiceMock
            ->shouldReceive('execute')
            ->once()
            ->with('user123')
            ->andThrow(new \Exception("Error inesperado", Response::HTTP_INTERNAL_SERVER_ERROR));
        $controller = new GetTimelineController($timelineServiceMock);
        $request = new GetTimelineRequest(['userId' => 'user123']);

        $response = $controller->__invoke($request);

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