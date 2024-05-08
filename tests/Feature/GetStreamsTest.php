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
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function get_streams_without_token()
    {
        $response = $this->get('/analytics/streams');

        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Unauthorized',
            'status' => 500,
            'message' => 'OAuth token is missing',
        ]);
    }
}
