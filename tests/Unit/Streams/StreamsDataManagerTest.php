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
     * @throws Exception
     */
    public function when_token_retrieval_successful_should_get_streams()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
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
    public function when_token_retrieval_failure_should_get_streams()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andThrow(new Exception('Error al obtener el token de Twitch', 500));

        try {
            $this->streamsDataManager->getStreams();
        } catch (Exception $result) {
            $this->assertEquals('Error al obtener el token de Twitch', $result->getMessage());
            $this->assertEquals(500, $result->getCode());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }

    /**
     * @test
     */
    public function when_api_client_error_should_get_streams()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andThrow(new Exception('Error al llamar a la API de Twitch', 500));

        try {
            $this->streamsDataManager->getStreams();
        } catch (Exception $result) {
            $this->assertEquals('Error al llamar a la API de Twitch', $result->getMessage());
            $this->assertEquals(500, $result->getCode());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }

    /**
     * @test
     */
    public function when_api_client_return_with_error_should_get_streams()
    {
        $this->twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');
        $this->apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->andReturn(['response' => null, 'status' => 500]);

        try {
            $this->streamsDataManager->getStreams();
        } catch (Exception $result) {
            $this->assertEquals(503, $result->getCode());
            $this->assertEquals('No se pueden devolver streams en este momento, inténtalo más tarde', $result->getMessage());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
