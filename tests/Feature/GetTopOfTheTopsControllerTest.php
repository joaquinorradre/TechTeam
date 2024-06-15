<?php

namespace Tests\Feature;

use App\Serializers\TopsOfTheTopsDataSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class GetTopOfTheTopsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getTopOfTheTops()
    {
        $response = $this->get('/analytics/tops', ['since' => 600]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name']
            ]
        ]);
    }
}