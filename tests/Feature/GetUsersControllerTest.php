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
    protected $apiClientMock;
    protected $dbClientMock;
    protected $twitchTokenServiceMock;
    protected $userDataSerializerMock;

    private $userDataManager;
    private $getUsersService;
    private $getUsersController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->dbClientMock = Mockery::mock(DbClient::class);
        $this->twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $this->userDataSerializerMock = Mockery::mock(UserDataSerializer::class);

        $this->userDataManager = new UserDataManager($this->apiClientMock,$this->dbClientMock,$this->twitchTokenServiceMock);
        $this->getUsersService = new GetUsersService($this->userDataManager);
        $this->getUsersController = new GetUsersController($this->getUsersService,$this->userDataSerializerMock);
    }
    /**
     * @test
     */
    public function getUsers()
    {
        $request = GetUsersRequest::create('/analytics/users', 'GET', ['id' => 'valor']);
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('token');
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andReturn(['response' => json_encode(['data' => [['id' => 'id', 'login' => 'login']]]), 'status' => 200]);
        $this->userDataSerializerMock
            ->shouldReceive('serialize')
            ->once()
            ->with([['id' => 'id', 'login' => 'login']])
            ->andReturn([['id' => 'id', 'login' => 'login',]]);

        $result = $this->getUsersController->__invoke($request);

        $this->assertNotEmpty($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}