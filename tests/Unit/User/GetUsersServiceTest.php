<?php

namespace Tests\Unit\User;

use App\Services\GetUsersService;
use App\Services\UserDataManager;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetUsersServiceTest extends TestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function given_a_correct_response_user_is_given()
    {
        $userDataManagerMock = Mockery::mock(UserDataManager::class);
        $userId = 29;
        $expectedResponse = json_encode(['data' => ['user_id' => 29, 'username' => 'example_user']]);
        $userDataManagerMock
            ->shouldReceive('getUserData')
            ->once()
            ->with($userId)
            ->andReturn($expectedResponse);
        $getUsersService = new GetUsersService($userDataManagerMock);

        $result = $getUsersService->execute($userId);

        $this->assertIsArray($result);
        $this->assertEquals(29, $result['user_id']);
        $this->assertEquals('example_user', $result['username']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function given_an_incorrect_response_should_return_error()
    {
        $userDataManagerMock = Mockery::mock(UserDataManager::class);
        $userId = 45;
        $userDataManagerMock
            ->shouldReceive('getUserData')
            ->once()
            ->with($userId)
            ->andReturn(json_encode(['data' => null]));
        $getUsersService = new GetUsersService($userDataManagerMock);

        $result = $getUsersService->execute($userId);

        $this->assertEmpty($result);
    }
}
