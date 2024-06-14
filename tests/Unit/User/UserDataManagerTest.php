<?php

namespace Tests\Unit\User;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TwitchTokenService;
use App\Services\UserDataManager;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UserDataManagerTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function given_a_correct_token_should_get_user_data()
    {
        $apiClientMock = $this->createMock(ApiClient::class);
        $dbClientMock = $this->createMock(DBClient::class);
        $twitchTokenServiceMock = $this->createMock(TwitchTokenService::class);

        $twitchTokenServiceMock
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('access_token');

        $apiClientMock
            ->expects($this->once())
            ->method('makeCurlCall')
            ->with('https://api.twitch.tv/helix/users?id=example_user_id', 'access_token')
            ->willReturn(['response' => json_encode(['data' => [['user_id' => 'example_user_id', 'username' => 'example_user']]]), 'status' => 200]);

        $userDataManager = new UserDataManager($apiClientMock, $dbClientMock, $twitchTokenServiceMock);
        $userId = 'example_user_id';

        $response = $userDataManager->getUserData($userId);

        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }

    /**
     * @test
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function given_a_failed_token_should_get_user_data()
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
            $this->assertEquals("Token de autenticación no proporcionado o inválido", $e->getMessage());
            $this->assertEquals(Response::HTTP_UNAUTHORIZED, $e->getCode());
        }
    }


    /**
     * @test
     */
    public function given_an_api_client_failure_should_get_user_data()
    {
        $apiClientMock = $this->createMock(ApiClient::class);
        $dbClientMock = $this->createMock(DBClient::class);
        $twitchTokenServiceMock = $this->createMock(TwitchTokenService::class);

        $twitchTokenServiceMock->method('getToken')->willReturn('access_token');
        $apiClientMock->method('makeCurlCall')->willThrowException(new Exception('Error al llamar a la API de Twitch', 500));

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

    /**
     * @test
     */
    public function given_an_api_client_return_with_internal_server_error_should_get_user_data()
    {
        $apiClientMock = $this->createMock(ApiClient::class);
        $dbClientMock = $this->createMock(DBClient::class);
        $twitchTokenServiceMock = $this->createMock(TwitchTokenService::class);

        $twitchTokenServiceMock->method('getToken')->willReturn('access_token');
        $apiClientMock->method('makeCurlCall')->willReturn(['response' => null, 'status' => 500]);

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
