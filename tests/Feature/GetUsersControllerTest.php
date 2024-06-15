<?php

namespace Tests\Feature;

use Mockery;
use PHPUnit\Framework\TestCase;

class GetUsersControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getUsers()
    {
        $response = $this->get('/analytics/users', ['id' => 'valor']);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'login']
            ]
        ]);

    }
}