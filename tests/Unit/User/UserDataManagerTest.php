<?php

namespace Tests\Unit\User;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TwitchTokenService;
use App\Services\UserDataManager;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

class UserDataManagerTest extends TestCase
{
    public function testGetUserDataWhenTheTokenIsCorrect()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/users?id=123', 'access_token')
            ->andReturn(['response' => json_encode(['data' => [['user_id' => 123, 'username' => 'example_user']]]), 'status' => 200]);

        $userDataManager = new UserDataManager($apiClientMock, $dbClientMock, $twitchTokenServiceMock);

        $result = $userDataManager->getUserData(123);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

}