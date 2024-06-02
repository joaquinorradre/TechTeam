<?php

namespace Tests\Unit\User;

use PHPUnit\Framework\TestCase;
use App\Services\CreateUserManager;
use App\Http\Clients\DBClient;
use Mockery;
use Exception;

class CreateUserManagerTest extends TestCase
{
    /**
     * @Test
     */
    public function givenExistingUserReturnsConflictException()
    {
        $username = 'existingUser';
        $password = 'password123';

        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock->shouldReceive('searchUser')
            ->with($username)
            ->andReturn(true);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Conflict: The username already exists');
        $this->expectExceptionCode(409);

        $createUserManager = new CreateUserManager($dbClientMock);
        $createUserManager->createUser($username, $password);
    }

    /**
     * @Test
     */
    public function givenNonExistingUserCreatesNewUser()
    {
        $username = 'newUser';
        $password = 'password123';

        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock->shouldReceive('searchUser')
            ->with($username)
            ->andReturn(false);
        $dbClientMock->shouldReceive('createUser')
            ->with($username, $password)
            ->andReturn(true);

        $createUserManager = new CreateUserManager($dbClientMock);
        $result = $createUserManager->createUser($username, $password);

        $this->assertTrue($result);
    }

    /**
     * @Test
     */
    public function givenNonExistingUserReturnsDBError()
    {
        $username = 'newUser';
        $password = 'password123';

        $dbClientMock = Mockery::mock(DBClient::class);
        $dbClientMock->shouldReceive('searchUser')
            ->with($username)
            ->andReturn(false);
        $dbClientMock->shouldReceive('createUser')
            ->with($username, $password)
            ->andThrow(new Exception('Database error', 500));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database error');
        $this->expectExceptionCode(500);

        $createUserManager = new CreateUserManager($dbClientMock);
        $createUserManager->createUser($username, $password);
    }
}