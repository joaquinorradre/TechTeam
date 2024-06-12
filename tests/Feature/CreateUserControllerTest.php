<?php

namespace Feature;

use App\Http\Controllers\CreateUserController;
use App\Http\Requests\CreateUserRequest;
use App\Services\CreateUserService;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;

class CreateUserControllerTest extends TestCase
{
    protected $createUserServiceMock;
    protected $createUserController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createUserServiceMock = Mockery::mock(CreateUserService::class);
        $this->createUserController = new CreateUserController($this->createUserServiceMock);
    }

    /**
     * @test
     */
    public function createUser()
    {
        $data = [
            'username' => 'nuevo_usuario',
            'password' => 'nueva_contraseña'
        ];
        $request = CreateUserRequest::create('/analytics/users', 'POST', $data);
        $this->createUserServiceMock
            ->shouldReceive('createUser')
            ->once()
            ->with('nuevo_usuario', 'nueva_contraseña')
            ->andReturn(true);


        $result = $this->createUserController->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'username' => 'nuevo_usuario',
            'message' => 'Usuario creado correctamente'
        ]), $result->getContent());
    }

    /**
     * @test
     */
    public function createUserWithConflict()
    {
        $data = [
            'username' => 'nuevo_usuario',
            'password' => 'nueva_contraseña'
        ];
        $request = CreateUserRequest::create('/analytics/users', 'POST', $data);
        $this->createUserServiceMock
            ->shouldReceive('createUser')
            ->once()
            ->with('nuevo_usuario', 'nueva_contraseña')
            ->andThrow(new \Exception('El nombre de usuario ya existe', 409));

        $result = $this->createUserController->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(409, $result->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'error' => 'Conflict',
            'message' => 'El nombre de usuario ya existe'
        ]), $result->getContent());
    }

    /**
     * @test
     */
    public function createUserWithServerError()
    {
        $data = [
            'username' => 'nuevo_usuario',
            'password' => 'nueva_contraseña'
        ];
        $request = CreateUserRequest::create('/analytics/users', 'POST', $data);
        $this->createUserServiceMock
            ->shouldReceive('createUser')
            ->once()
            ->with('nuevo_usuario', 'nueva_contraseña')
            ->andThrow(new \Exception('Error del servidor al crear el usuario', 500));

        $result = $this->createUserController->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(500, $result->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'error' => 'Internal Server Error',
            'message' => 'Error del servidor al crear el usuario'
        ]), $result->getContent());
    }

    /**
     * @test
     */
    public function createUserWithMissingParameters()
    {
        $request = CreateUserRequest::create('/analytics/users', 'POST');
        $result = $this->createUserController->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'error' => 'Bad Request',
            'message' => 'Parámetros inválidos'
        ]), $result->getContent());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}