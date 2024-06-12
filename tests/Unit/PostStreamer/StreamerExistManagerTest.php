<?php

namespace Tests\Unit\PostStreamer;

use App\Http\Clients\ApiClient;
use App\Services\StreamerExistManager;
use App\Services\TwitchTokenService;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class StreamerExistManagerTest extends TestCase
{
    private $twitchTokenServiceMock;
    private $apiClientMock;
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);
        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->manager = new StreamerExistManager($this->twitchTokenServiceMock, $this->apiClientMock);
    }

    /**
     * @test
     * @throws Exception
     */
    public function when_streamer_exists_should_return_true()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('fake_token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/users?id=123', 'fake_token')
            ->andReturn([
                'status' => 200,
                'response' => json_encode(['data' => [['id' => '123']]]),
            ]);

        $result = $this->manager->getStreamer('123');

        $this->assertTrue($result);
    }

    /**
     * @test
     * @throws Exception
     */
    public function when_streamer_does_not_exist_should_return_false()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('fake_token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/users?id=123', 'fake_token')
            ->andReturn([
                'status' => 200,
                'response' => json_encode(['data' => []]),
            ]);

        $result = $this->manager->getStreamer('123');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function when_api_call_fails_should_throw_exception()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('fake_token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/users?id=123', 'fake_token')
            ->andThrow(new Exception('Test Exception', 500));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test Exception');
        $this->expectExceptionCode(500);

        $this->manager->getStreamer('123');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
