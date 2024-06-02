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
    /**
     * @test
     * @throws Exception
     */
    public function when_streamer_exists_should_return_true()
    {
        $twitchTokenService = Mockery::mock(TwitchTokenService::class);
        $twitchTokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('fake_token');
        $apiClient = Mockery::mock(ApiClient::class);
        $apiClient
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/users?id=123', 'fake_token')
            ->andReturn([
                'status' => 200,
                'response' => json_encode(['data' => [['id' => '123']]]),
            ]);
        $manager = new StreamerExistManager($twitchTokenService, $apiClient);

        $result = $manager->getStreamer('123');

        $this->assertTrue($result);
    }

    /**
     * @test
     * @throws Exception
     */
    public function when_streamer_does_not_exist_should_return_false()
    {
        $twitchTokenService = Mockery::mock(TwitchTokenService::class);
        $twitchTokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('fake_token');
        $apiClient = Mockery::mock(ApiClient::class);
        $apiClient
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/users?id=123', 'fake_token')
            ->andReturn([
                'status' => 200,
                'response' => json_encode(['data' => []]),
            ]);
        $manager = new StreamerExistManager($twitchTokenService, $apiClient);

        $result = $manager->getStreamer('123');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function when_api_call_fails_should_throw_exception()
    {
        $twitchTokenService = Mockery::mock(TwitchTokenService::class);
        $twitchTokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('fake_token');
        $apiClient = Mockery::mock(ApiClient::class);
        $apiClient
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/users?id=123', 'fake_token')
            ->andThrow(new Exception('Test Exception', 500));
        $manager = new StreamerExistManager($twitchTokenService, $apiClient);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test Exception');
        $this->expectExceptionCode(500);

        $manager->getStreamer('123');
    }
}