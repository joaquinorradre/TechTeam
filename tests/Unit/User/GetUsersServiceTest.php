<?php

namespace Tests\Unit\User;

use App\Services\GetUsersService;
use App\Services\UserDataManager;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetUsersServiceTest extends TestCase
{
    protected $userDataManagerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userDataManagerMock = Mockery::mock(UserDataManager::class);
        $this->getUsersService = new GetUsersService($this->userDataManagerMock);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function given_a_correct_response_user_is_given()
    {
        $userId = 29;
        $expectedResponse = json_encode(['data' => ['user_id' => 29, 'username' => 'example_user']]);
        $this->userDataManagerMock
            ->shouldReceive('getUserData')
            ->once()
            ->with($userId)
            ->andReturn($expectedResponse);

        $result = $this->getUsersService->execute($userId);

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
        $userId = 45;
        $this->userDataManagerMock
            ->shouldReceive('getUserData')
            ->once()
            ->with($userId)
            ->andReturn(json_encode(['data' => null]));

        $result = $this->getUsersService->execute($userId);

        $this->assertEmpty($result);
    }
}

