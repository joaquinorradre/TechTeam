<?php

namespace Tests\Unit\User;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TwitchTokenService;
use App\Services\UserDataManager;
use Illuminate\Http\Response;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

class UserDataManagerTest extends TestCase
{
    public function testGetUserDataWhenGetTokenIsCorrect()
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

        $response = $userDataManager->getUserData(123);

        // Verificar que el objeto devuelto es una instancia de Response
        $this->assertInstanceOf(Response::class, $response);

        // Verificar que la respuesta no está vacía
        $this->assertNotEmpty($response->getContent());

        // Verificar el código de estado de la respuesta
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetUserDataWhenGetTokenFails()
    {
        $apiClientMock = $this->createMock(ApiClient::class);
        $dbClientMock = $this->createMock(DBClient::class);

        $dbClientMock->expects($this->once())
            ->method('getTokenFromDatabase')
            ->willReturn(null);

        $apiClientMock->expects($this->once())
            ->method('getTokenFromAPI')
            ->willThrowException(new Exception("Error al obtener el token de la API de Twitch"));

        $userDataManager = new UserDataManager($apiClientMock, $dbClientMock, new TwitchTokenService($dbClientMock, $apiClientMock));

        try {
            $userId = 'example_user_id';
            $userDataManager->getUserData($userId);
            $this->fail("Se esperaba una excepción al obtener el token");
        }
        catch (Exception $e) {
            $this->assertEquals("No se puede establecer conexión con Twitch en este momento", $e->getMessage());
            $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $e->getCode());
        }
    }

    public function testGetUserDataWhenApiClientFails()
    {
        $apiClientMock = Mockery::mock(ApiClient::class);
        $dbClientMock = Mockery::mock(DBClient::class);
        $twitchTokenServiceMock = Mockery::mock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->shouldReceive('getToken')
            ->andReturn('access_token');

        $apiClientMock
            ->shouldReceive('makeCurlCall')
            ->andThrow(new Exception('Error al llamar a la API de Twitch', 500));

        $userDataManager = new UserDataManager($apiClientMock, $dbClientMock, $twitchTokenServiceMock);

        try {
            $userDataManager->getUserData(123);
        } catch (\Exception $result) {
            $this->assertEquals('Error al llamar a la API de Twitch', $result->getMessage());
            $this->assertEquals(500, $result->getCode());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }

    public function testGetUserDataWhenApiClientReturnsInternalServerError()
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
            ->andReturn(['response' => null, 'status' => 500]);

        $userDataManager = new UserDataManager($apiClientMock, $dbClientMock, $twitchTokenServiceMock);

        try {
            $userDataManager->getUserData(123);
        } catch (\Exception $result) {
            $this->assertEquals(503, $result->getCode());
            $this->assertEquals('No se pueden devolver usuarios en este momento, inténtalo más tarde', $result->getMessage());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }

}