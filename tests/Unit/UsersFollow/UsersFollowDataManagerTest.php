<?php

namespace Tests\Unit\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TwitchTokenService;
use App\Services\UsersFollowDataManager;
use App\Serializers\UsersFollowSerializer;
use Mockery;
use PHPUnit\Framework\TestCase;

class UsersFollowDataManagerTest extends TestCase
{
    private $apiClientMock;
    private $dbClientMock;
    private $twitchTokenServiceMock;
    private $serializerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $this->serializerMock = Mockery::mock(UsersFollowSerializer::class);
        $this->userDataManager = new UsersFollowDataManager($this->apiClientMock, $this->twitchTokenServiceMock, $this->dbClientMock, $this->serializerMock);

    }

    /**
     * @test
     */
    public function given_a_correct_token_and_correct_api_response_do_get_user_data()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');

        $this->dbClientMock
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

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with("https://api.twitch.tv/helix/users?id=123", 'access_token')
            ->andReturn(['response' => json_encode(['data' => [['login' => 'streamer1']]]), 'status' => 200]);

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->with("https://api.twitch.tv/helix/users?id=456", 'access_token')
            ->andReturn(['response' => json_encode(['data' => [['login' => 'streamer2']]]), 'status' => 200]);

        $this->serializerMock
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


        $response = $this->userDataManager->getUserData();

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
