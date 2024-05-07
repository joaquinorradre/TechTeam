<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Clients\ApiClient;
use App\Services\StreamsDataManager;
use App\Http\Controllers\GetStreams;
use App\Services\GetStreamsService;
use Mockery;
use Tests\TestCase;
use Illuminate\Http\Request;

class GetStreamsTest extends TestCase
{
    /**
     * @test
     **/
    public function testErrorHandling()
    {
        $mockGetStreamsService = Mockery::mock(GetStreamsService::class);
        $mockGetStreamsService->shouldReceive('execute')->andThrow(new \Exception('Service Unavailable', 503));

        $controller = new GetStreams($mockGetStreamsService);

        $response = $controller->__invoke();

        $response->assertJson([
            'error' => 'No se pueden devolver usuarios en este momento, inténtalo más tarde'
        ]);

        $response->assertStatus(503);
    }
}
