<?php

namespace Feature;

use App\Services\CreateUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();


    }
    /**
     * @test
     */
    public function createsUser()
    {

        $response = $this->postJson('/analytics/users', [
            'username' => 'nuevo_usuario',
            'password' => 'nueva_contraseña'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'username' => 'nuevo_usuario',
                'message' => 'Usuario creado correctamente'
            ]);
        $this->assertDatabaseHas('users', [
            'username' => 'nuevo_usuario',
        ]);
    }

    /**
     * @test
     */
    public function doesNotCreateAUserIfAlreadyExists()
    {
        $username = 'nuevo_usuario';
        $password = 'nueva_contraseña';

        User::create([
            'name' => $username,
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson('/analytics/users', [
            'username' => $username,
            'password' => $password
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Conflict',
                'message' => ' El nombre de usuario ya está en uso.'
            ]);
    }

    /**
     * @test
     */
    public function doesNotCreateUserIfServerError()
    {
        $this->app->bind(CreateUserService::class, function ($app) {
            return new class {
                public function createUser($username, $password)
                {
                    throw new \Exception('Error del servidor al crear el usuario.', 500);
                }
            };
        });

        $username = 'nuevo_usuario';
        $password = 'nueva_contraseña';

        $response = $this->postJson('/analytics/users', [
            'username' => $username,
            'password' => $password
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Internal Server Error',
                'message' => 'Error del servidor al crear el usuario.'
            ]);
    }

    /**
     * @test
     */
    public function doesNotCreateUserIfMissingParameters()
    {
        $response = $this->postJson('/analytics/users', []);

        // Verifica que la respuesta sea la esperada
        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Bad Request',
                'message' => 'Los parámetros requeridos ( username y password ) no fueron proporcionados.'
            ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}