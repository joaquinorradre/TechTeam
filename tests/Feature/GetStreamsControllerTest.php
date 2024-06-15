<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetStreamsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getStreams()
    {
        $response = $this->get('/analytics/streams');

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                ['title' => 'Stream 1', 'user_name' => 'User 1']
            ]
        ]);
    }
}