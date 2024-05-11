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
    public function testGetStreamsWhenTwitchTokenIsRetrievedSuccessfully()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->with('https://api.twitch.tv/helix/streams', 'access_token')
            ->andReturn(['response' => json_encode(['data' => [['title' => 'Stream 1', 'user_name' => 'User 1']]]), 'status' => 200]);

        $streamsDataManager = new StreamsDataManager($apiClientMock, $twitchTokenServiceMock);

        $result = $streamsDataManager->getStreams();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testGetStreamsWhenTwitchTokenRetrievalFails()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andThrow(new Exception('Error al obtener el token de Twitch', 500));

        $streamsDataManager = new StreamsDataManager($apiClientMock, $twitchTokenServiceMock);

        try {
            $streamsDataManager->getStreams();
        } catch (\Exception $result) {
            $this->assertEquals('Error al obtener el token de Twitch', $result->getMessage());
            $this->assertEquals(500, $result->getCode());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }




    public function testGetStreamsWhenApiClientFails()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andThrow(new Exception('Error al llamar a la API de Twitch', 500));

        $streamsDataManager = new StreamsDataManager($apiClientMock, $twitchTokenServiceMock);

        try {
            $streamsDataManager->getStreams();
        } catch (\Exception $result) {
            $this->assertEquals('Error al llamar a la API de Twitch', $result->getMessage()); // Verifica el mensaje de la excepción
            $this->assertEquals(500, $result->getCode()); // Verifica el código de la excepción
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }


    public function testGetStreamsWhenApiClientReturnsInternalServerError()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->once()
            ->andReturn(['response' => null, 'status' => 500]);

        $streamsDataManager = new StreamsDataManager($apiClientMock, $twitchTokenServiceMock);

        try {
            $streamsDataManager->getStreams();
        } catch (\Exception $result) {
            $this->assertEquals(503, $result->getCode());
            $this->assertEquals('No se pueden devolver streams en este momento, inténtalo más tarde', $result->getMessage());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }


}

