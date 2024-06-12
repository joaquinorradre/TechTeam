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
    protected $apiClientMock;
    protected $dbClientMock;
    protected $twitchTokenServiceMock;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClientMock = $this->createMock(ApiClient::class);
        $this->dbClientMock = $this->createMock(DBClient::class);
        $this->twitchTokenServiceMock = $this->createMock(TwitchTokenService::class);
        $this->userDataManager = new UserDataManager($this->apiClientMock, $this->dbClientMock, $this->twitchTokenServiceMock);
    }

    /**
     * @test
     * @throws Exception
     */
    public function given_a_correct_token_should_get_user_data()
    {
        $this->twitchTokenServiceMock
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('access_token');

        $this->apiClientMock
            ->expects($this->once())
            ->method('makeCurlCall')
            ->with('https://api.twitch.tv/helix/users?id=example_user_id', 'access_token')
            ->willReturn(['response' => json_encode(['data' => [['user_id' => 'example_user_id', 'username' => 'example_user']]]), 'status' => 200]);

        $userId = 'example_user_id';

        $response = $this->userDataManager->getUserData($userId);

        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }

    /**
     * @test
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function given_a_failed_token_should_get_user_data()
    {
        $this->dbClientMock->expects($this->once())
            ->method('getTokenFromDatabase')
            ->willReturn(null);
        $this->apiClientMock->expects($this->once())
            ->method('getTokenFromAPI')
            ->willThrowException(new Exception("Error al obtener el token de la API de Twitch"));

        $twitchTokenService = new TwitchTokenService($this->dbClientMock, $this->apiClientMock);
        $userDataManager = new UserDataManager($this->apiClientMock, $this->dbClientMock, $twitchTokenService);

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
        $this->twitchTokenServiceMock->method('getToken')->willReturn('access_token');
        $this->apiClientMock->method('makeCurlCall')->willThrowException(new Exception('Error al llamar a la API de Twitch', 500));


        try {
            $this->userDataManager->getUserData(123);
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
        $this->twitchTokenServiceMock->method('getToken')->willReturn('access_token');
        $this->apiClientMock->method('makeCurlCall')->willReturn(['response' => null, 'status' => 500]);


        try {
            $this->userDataManager->getUserData(123);
        } catch (\Exception $result) {
            $this->assertEquals(503, $result->getCode());
            $this->assertEquals('No se pueden devolver usuarios en este momento, inténtalo más tarde', $result->getMessage());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }
}
