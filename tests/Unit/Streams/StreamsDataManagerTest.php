<?php

namespace Tests\Unit\Streams;

use App\Http\Clients\ApiClient;
use App\Services\StreamsDataManager;
use App\Services\TwitchTokenService;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

class StreamsDataManagerTest extends TestCase
{
    private $apiClientMock;
    private $twitchTokenServiceMock;
    private $streamsDataManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = Mockery::mock(ApiClient::class);
        $this->twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $this->streamsDataManager = new StreamsDataManager($this->apiClientMock, $this->twitchTokenServiceMock);
    }

    /**
     * @test
     */
    public function it_returns_streams_data_when_token_retrieval_is_successful()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('access_token');

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/streams', 'access_token')
            ->andReturn(['response' => json_encode(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]), 'status' => 200]);

        $result = $this->streamsDataManager->getStreams();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_token_retrieval_fails()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andThrow(new Exception('Error al obtener el token de Twitch', 500));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error al obtener el token de Twitch');
        $this->expectExceptionCode(500);

        $this->streamsDataManager->getStreams();
    }

    /**
     * @test
     */
    public function it_throws_exception_when_api_call_fails()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('access_token');

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->andThrow(new Exception('Error al llamar a la API de Twitch', 500));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error al llamar a la API de Twitch');
        $this->expectExceptionCode(500);

        $this->streamsDataManager->getStreams();
    }

    /**
     * @test
     */
    public function it_throws_exception_when_api_returns_error_status()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->once()
            ->andReturn('access_token');

        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->andReturn(['response' => null, 'status' => 500]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No se pueden devolver streams en este momento, inténtalo más tarde');
        $this->expectExceptionCode(503);

        $this->streamsDataManager->getStreams();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}