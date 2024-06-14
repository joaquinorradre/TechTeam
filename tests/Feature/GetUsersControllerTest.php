<?php

namespace Tests\Feature;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Http\Controllers\GetUsersController;
use App\Http\Requests\GetUsersRequest;
use App\Serializers\UserDataSerializer;
use App\Services\GetUsersService;
use App\Services\TwitchTokenService;
use App\Services\UserDataManager;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetUsersControllerTest extends TestCase
{
    /**
     * @test
     */
    public function getUsers()
    {
        $request = GetUsersRequest::create('/analytics/users', 'GET', ['id' => 'valor']);
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $dbClientMock = Mockery::mock(DbClient::class);
        $userDataSerializerMock = Mockery::mock(UserDataSerializer::class);
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
            ->with([['id' => 'id', 'login' => 'login']])
            ->andReturn([['id' => 'id', 'login' => 'login',]]);
        $userDataManager = new UserDataManager($apiClientMock,$dbClientMock,$twitchTokenServiceMock);
        $getUsersService = new GetUsersService($userDataManager);
        $getUsersController = new GetUsersController($getUsersService,$userDataSerializerMock);

        $result = $getUsersController->__invoke($request);

        $this->assertNotEmpty($result);
    }
}