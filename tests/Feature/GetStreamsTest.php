<?php

namespace Tests\Feature;

use App\Http\Controllers\GetStreamsController;
use App\Services\GetStreamsService;
use Mockery;
use Tests\TestCase;

class GetStreamsTest extends TestCase
{
    /**
     * @test
     **/
    public function testErrorHandling()
    {
        $mockGetStreamsService = Mockery::mock(GetStreamsService::class);
        $mockGetStreamsService->shouldReceive('execute')->andThrow(new \Exception('Service Unavailable', 503));

        $controller = new GetStreamsController($mockGetStreamsService);

        $response = $controller->__invoke();

        $response->assertJson([
            'error' => 'No se pueden devolver usuarios en este momento, inténtalo más tarde'
        ]);

        $response->assertStatus(503);
    }
}
