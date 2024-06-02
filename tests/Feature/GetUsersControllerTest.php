<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Http\Controllers\GetUsersController;
use App\Http\Requests\GetTimelineRequest;
use App\Serializers\UserListSerializer;
use App\Services\GetUsersService;
use App\Services\UserDataManager;
use App\Services\TwitchTokenService;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetUsersControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getUsersControllerIntegrationTest()
    {
        $request = GetTimelineRequest::create('/analytics/users', 'GET', ['id' => 'valor']);

        $apiClientMock = Mockery::mock(ApiClient::class);
        $userDataManagerMock = Mockery::mock(UserDataManager::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $dbClientMock = Mockery::mock(DbClient::class);
        $userDataSerializerMock = Mockery::mock(UserListSerializer::class);
        $getUsersServiceMock = Mockery::mock(GetUsersService::class);

        $getUsersServiceMock
            ->shouldReceive('execute')
            ->with('valor')
            ->once()
            ->andReturn(['data' => [['id' => 'id', 'login' => 'login']]]);

        $userDataManagerMock
            ->shouldReceive('getUserData')
            ->with('valor')
            ->once()
            ->andReturn(json_encode(['data' => [['id' => 'id', 'login' => 'login']]]));

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('token');

        $dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode(['data' => [['id' => 'id', 'login' => 'login']]]), 'status' => 200]);

        $userDataSerializerMock
            ->shouldReceive('serialize')
            ->once()
            ->with(['data' => [['id' => 'id', 'login' => 'login']]])
            ->andReturn([['id' => 'id', 'login' => 'login',]]);

        $getUsersController = new GetUsersController($getUsersServiceMock,$userDataSerializerMock);

        $result = $getUsersController->__invoke($request);

        $this->assertNotEmpty($result);
    }
}
