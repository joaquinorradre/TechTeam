<?php

namespace Tests\Unit\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TwitchTokenService;
use App\Services\UsersFollowDataManager;
use App\Serializers\UserDataSerializer;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UsersFollowDataManagerTest extends TestCase
{
    /**
     * @test
     */
    public function givenACorrectTokenAndApiResponseDoGetUserData()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $serializerMock = Mockery::mock(UserDataSerializer::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');

        $dbClientMock
            ->shouldReceive('getUsersWithFollowedStreamers')
            ->once()
            ->andReturn([
                (object)[
                    'username' => 'user1',
                    'streamerId' => 123
                ],
                (object)[
                    'username' => 'user2',
                    'streamerId' => 456
                ]
            ]);

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with("https://api.twitch.tv/helix/users?id=123", 'access_token')
            ->andReturn(['response' => json_encode(['data' => [['login' => 'streamer1']]]), 'status' => 200]);

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with("https://api.twitch.tv/helix/users?id=456", 'access_token')
            ->andReturn(['response' => json_encode(['data' => [['login' => 'streamer2']]]), 'status' => 200]);

        $serializerMock
            ->shouldReceive('serialize')
            ->once()
            ->with([
                'user1' => ['streamer1'],
                'user2' => ['streamer2']
            ])
            ->andReturn([
                ['username' => 'user1', 'followedStreamers' => ['streamer1']],
                ['username' => 'user2', 'followedStreamers' => ['streamer2']]
            ]);

        $userDataManager = new UsersFollowDataManager($apiClientMock, $twitchTokenServiceMock, $dbClientMock, $serializerMock);

        $response = $userDataManager->getUserData();

        $this->assertIsArray($response);
        $this->assertCount(2, $response);
        $this->assertEquals('user1', $response[0]['username']);
        $this->assertEquals(['streamer1'], $response[0]['followedStreamers']);
        $this->assertEquals('user2', $response[1]['username']);
        $this->assertEquals(['streamer2'], $response[1]['followedStreamers']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}