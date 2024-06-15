<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Services\StreamerExistManager;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Mockery;

class PostStreamerControllerTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario de prueba en la base de datos
        User::factory()->create([
            'username' => 'user123',
            'password' => bcrypt('password123'),
        ]);
    }

    protected function mockApiClientSuccess()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode(['data' => [['id' => '123']]]), 'status' => 200]);

        $this->app->instance(ApiClient::class, $apiClientMock);
    }

    protected function mockStreamerExistManager(bool $exists)
    {
        $streamerExistManagerMock = Mockery::mock(StreamerExistManager::class);
        $streamerExistManagerMock
            ->shouldReceive('getStreamer')
            ->once()
            ->with('streamer123')
            ->andReturn($exists);

        $this->app->instance(StreamerExistManager::class, $streamerExistManagerMock);
    }

    /**
     * @test
     */
    public function postsStreamer()
    {
        $this->mockApiClientSuccess();
        $this->mockStreamerExistManager(true);

        $response = $this->postJson('/analytics/follow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ahora sigues a streamer123'
            ]);
    }

    /**
     * @test
     */
    public function doesNotFollowStreamerIfServerError()
    {
        $response = $this->post('/analytics/follow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123',
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'message' => 'Error del servidor al seguir al streamer.',
            ]);
    }

    /**
     * @test
     */
    public function doesNotFollowStreamerIfStreamerNotFound()
    {
        try {
            $response = $this->postJson('/analytics/follow', [
                'userId' => 'user123',
                'streamerId' => 'streamer123',
            ]);

            $this->fail('La excepción esperada no se ha lanzado');
        } catch (StreamerNotFoundException $e) {
            $this->assertInstanceOf(StreamerNotFoundException::class, $e);

            $this->assertSame(Response::HTTP_NOT_FOUND, $e->getStatusCode());

            $this->assertSame('El usuario user123 o el streamer streamer123 especificado no existe en la API.', $e->getMessage());
        }
    }

    public function doesNotFollowStreamerIfStreamerAlreadyFollowed()
    {
        UserFollow::create([
            'username' => 'user123',
            'streamer_id' => 'streamer123',
        ]);

        $response = $this->postJson('/analytics/follow', [
            'userId' => 'user123',
            'streamerId' => 'streamer123',
        ]);

        $response->assertStatus(Response::HTTP_CONFLICT);

        $response->assertJson([
            'error' => 'Conflict',
            'message' => 'El usuario ya está siguiendo al streamer.',
        ]);
    }
}