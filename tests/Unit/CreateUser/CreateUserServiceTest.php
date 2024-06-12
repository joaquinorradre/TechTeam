<?php

namespace Tests\Unit\CreateUser;

use App\Services\CreateUserService;
use PHPUnit\Framework\TestCase;
use App\Http\Clients\DBClient;
use Mockery;
use Exception;

class CreateUserServiceTest extends TestCase
{
    private $dbClientMock;
    private $createUserService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->createUserService = new CreateUserService($this->dbClientMock);
    }

    /**
     * @test
     * @throws Exception
     */
    public function when_username_is_unique_should_create_user_successfully()
    {
        $this->dbClientMock
            ->shouldReceive('userExistsInDatabase')
            ->once()
            ->with('nuevo_usuario')
            ->andReturn(false);
        $this->dbClientMock
            ->shouldReceive('createUser')
            ->once()
            ->with('nuevo_usuario', 'nueva_contraseña')
            ->andReturnNull(); // No devuelve nada explícitamente

        $result = $this->createUserService->createUser('nuevo_usuario', 'nueva_contraseña');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function when_username_exists_should_throw_conflict_exception()
    {
        $this->dbClientMock
            ->shouldReceive('userExistsInDatabase')
            ->once()
            ->with('nuevo_usuario')
            ->andReturn(true);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('El nombre de usuario ya existe');
        $this->expectExceptionCode(409);

        $this->createUserService->createUser('nuevo_usuario', 'nueva_contraseña');
    }

    /**
     * @test
     */
    public function when_database_error_should_throw_internal_server_error_exception()
    {
        $this->dbClientMock
            ->shouldReceive('userExistsInDatabase')
            ->once()
            ->with('nuevo_usuario')
            ->andReturn(false);
        $this->dbClientMock
            ->shouldReceive('createUser')
            ->once()
            ->with('nuevo_usuario', 'nueva_contraseña')
            ->andThrow(new Exception('Error del servidor al crear el usuario', 500));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error del servidor al crear el usuario');
        $this->expectExceptionCode(500);

        $this->createUserService->createUser('nuevo_usuario', 'nueva_contraseña');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
