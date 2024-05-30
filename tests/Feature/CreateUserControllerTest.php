<?php

use PHPUnit\Framework\TestCase;
use Mockery;
use App\Services\CreateUserManager;
class CreateUserControllerTest extends TestCase
{
    /**
     * @Test
     */
    public function createUserSuccesfully()
    {
        $username = 'newUser';
        $password = 'password123';

        $createUserManagerMock = Mockery::mock(CreateUserManager::class);
        $createUserManagerMock->shouldReceive('createUser')
            ->once()
            ->with($username, $password)
            ->andReturn(true);

        $this->app->instance(CreateUserManager::class, $createUserManagerMock);

        $response = $this->postJson('/analytics/users', [
            'username' => $username,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'username' => $username,
                'message' => 'Usuario creado correctamente',
            ]);
    }

    /**
     * @Test
     */
    public function createExistingUserReturnsConflict()
    {
        $username = 'existingUser';
        $password = 'password123';

        $createUserManagerMock = Mockery::mock(CreateUserManager::class);
        $createUserManagerMock->shouldReceive('createUser')
            ->once()
            ->with($username, $password)
            ->andThrow(new \Exception('Conflict: The username already exists', 409));

        $this->app->instance(CreateUserManager::class, $createUserManagerMock);

        $response = $this->postJson('/analytics/users', [
            'username' => $username,
            'password' => $password,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'error' => 'Conflict',
                'message' => 'El nombre de usuario ya existe',
            ]);
    }

    /**
     * @Test
     */
    public function createUserReturnsInternalError()
    {
        $username = 'newUser';
        $password = 'password123';

        $createUserManagerMock = Mockery::mock(CreateUserManager::class);
        $createUserManagerMock->shouldReceive('createUser')
            ->once()
            ->with($username, $password)
            ->andThrow(new \Exception('Error del servidor al crear el usuario', 500));

        $this->app->instance(CreateUserManager::class, $createUserManagerMock);

        $response = $this->postJson('/analytics/users', [
            'username' => $username,
            'password' => $password,
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Internal Server Error',
                'message' => 'Error del servidor al crear el usuario',
            ]);
    }
}