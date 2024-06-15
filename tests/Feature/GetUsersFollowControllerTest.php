<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetUsersFollowControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuarios y sus streamers seguidos en la base de datos
        DB::table('User')->insert([
            ['username' => 'usuario1', 'password' => 'hashed_password1'],
            ['username' => 'usuario2', 'password' => 'hashed_password2'],
        ]);

        DB::table('user_follow')->insert([
            ['username' => 'usuario1', 'streamerId' => 'streamer1'],
            ['username' => 'usuario1', 'streamerId' => 'streamer2'],
            ['username' => 'usuario2', 'streamerId' => 'streamer2'],
            ['username' => 'usuario2', 'streamerId' => 'streamer3'],
        ]);

    }
    /**
     * @test
     */
    public function getsUsersAndFollowedStreamers()
    {
        $response = $this->get('/analytics/users');

        $response->assertStatus(200);

        $responseData = $response->json();

        $this->assertIsArray($responseData);
        foreach ($responseData as $user) {
            $this->assertArrayHasKey('username', $user);
            $this->assertArrayHasKey('followedStreamers', $user);
            $this->assertIsArray($user['followedStreamers']);
        }
    }

    /**
     * @test
     */
    public function doesNotGetUsersAndFollowedStreamersIfServerError()
    {
        $response = $this->get('/analytics/users', ['forceError' => true]);

        $response->assertStatus(500);

        $response->assertJson([
            'error' => 'Error del servidor al obtener la lista de usuarios.'
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}