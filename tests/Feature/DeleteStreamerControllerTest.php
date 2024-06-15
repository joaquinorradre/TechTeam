<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;

class DeleteStreamerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('users')->insert([
            'username' => 'user123',
            'password' => bcrypt('password123'),
        ]);

        DB::table('user_follows')->insert([
            'username' => 'user123',
            'streamer_id' => 'streamer123',
        ]);
    }
    /**
     * @test
     */
    public function unfollowsStreamer()
    {
        $response = $this->delete('/analytics/unfollow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Dejaste de seguir a streamer123',
            ]);

        $this->assertDatabaseMissing('user_follows', [
            'username' => 'user123',
            'streamer_id' => 'streamer123',
        ]);
    }

    /**
     * @test
     */
    public function doesNotUnfollowStreamerIfServerError()
    {
        $user = User::factory()->create([
            'username' => 'user123',
            'password' => Hash::make('password'),
        ]);

        UserFollow::create([
            'username' => 'user123',
            'streamer_id' => 'streamer123',
        ]);

        DB::shouldReceive('table->where->delete')
            ->once()
            ->with('user_follows', [
                'username' => 'user123',
                'streamer_id' => 'streamer123',
            ])
            ->andThrow(new \Exception('Error del servidor al dejar de seguir al streamer.', Response::HTTP_INTERNAL_SERVER_ERROR));

        $response = $this->actingAs($user)->deleteJson('/analytics/unfollow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123',
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'error' => 'Internal Server Error',
                'message' => 'Error del servidor al dejar de seguir al streamer.'
            ]);
    }


    /**
     * @test
     */
    public function doesNotUnfollowStreamerIfStreamerNotFound()
    {
        $user = User::factory()->create([
            'username' => 'user123',
            'password' => Hash::make('password'),
        ]);

        DB::shouldReceive('table->where->delete')
            ->once()
            ->with('user_follows', [
                'username' => 'user123',
                'streamer_id' => 'streamer123',
            ])
            ->andThrow(new \Exception('El usuario user123 o el streamer streamer123 especificado no existe en la API.', Response::HTTP_NOT_FOUND));

        $response = $this->actingAs($user)->deleteJson('/analytics/unfollow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123',
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'error' => 'Not Found',
                'message' => 'El usuario user123 o el streamer streamer123 especificado no existe en la API.'
            ]);
    }

    public function doesNotUnfollowStreamerIfUserNotFound()
    {
        $user = User::factory()->create([
            'username' => 'user123',
            'password' => Hash::make('password'),
        ]);

        DB::shouldReceive('table->where->delete')
            ->once()
            ->with('user_follows', [
                'username' => 'user123',
                'streamer_id' => 'streamer123',
            ])
            ->andThrow(new \Exception('El usuario user123 o el streamer streamer123 especificado no existe en la API.', Response::HTTP_NOT_FOUND));

        // Realizar una solicitud HTTP DELETE al endpoint utilizando Laravel Testing helpers
        $response = $this->actingAs($user)->deleteJson('/analytics/unfollow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123',
        ]);

        // Verificar que la respuesta sea una instancia de JsonResponse y tenga el estado HTTP adecuado
        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'error' => 'Not Found',
                'message' => 'El usuario user123 o el streamer streamer123 especificado no existe en la API.'
            ]);
    }

    protected function tearDown(): void
    {
        UserFollow::truncate();
        User::truncate();
        parent::tearDown();

    }
}