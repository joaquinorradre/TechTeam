<?php

namespace Tests\Feature;

use App\Http\Clients\DBClient;
use Mockery;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    protected $dbClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClientMock = Mockery::mock(DBClient::class);

        $this->app->instance(DBClient::class, $this->dbClientMock);
    }

    /**
     * @test
     */
    public function createUser()
    {
        $this->dbClientMock
            ->shouldReceive('createUser')
            ->once()
            ->with('nuevo_usuario', 'nueva_contraseña')
            ->andReturn(null);

        $this->dbClientMock
            ->shouldReceive('userExistsInDatabase')
            ->once()
            ->with('nuevo_usuario')
            ->andReturn(false);
        $response = $this->postJson('/analytics/users', [
            'username' => 'nuevo_usuario',
            'password' => 'nueva_contraseña'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'username' => 'nuevo_usuario',
                'message' => 'Usuario creado correctamente'
            ]);
    }

    /**
     * @test
     */
    public function createUserWithConflict()
    {
        $this->dbClientMock
            ->shouldReceive('userExistsInDatabase')
            ->once()
            ->with('nuevo_usuario')
            ->andReturn(true);

        $response = $this->postJson('/analytics/users', [
            'username' => 'nuevo_usuario',
            'password' => 'nueva_contraseña'
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Conflict',
                'message' => 'El nombre de usuario ya existe'
            ]);
    }

    /**
     * @test
     */
    public function createUserWithServerError()
    {
        $this->dbClientMock
            ->shouldReceive('createUser')
            ->once()
            ->andThrow(new \Exception('Error del servidor al crear el usuario', 500));

        $this->dbClientMock
            ->shouldReceive('userExistsInDatabase')
            ->once()
            ->with('nuevo_usuario')
            ->andReturn(false);

        $response = $this->postJson('/analytics/users', [
            'username' => 'nuevo_usuario',
            'password' => 'nueva_contraseña'
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Internal Server Error',
                'message' => 'Error del servidor al crear el usuario'
            ]);
    }

    /**
     * @test
     */
    public function createUserWithMissingParameters()
    {
        $response = $this->postJson('/analytics/users', []);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Bad Request',
                'message' => 'Parámetros inválidos'
            ]);
    }


    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}

