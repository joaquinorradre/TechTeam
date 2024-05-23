<?php
namespace Tests\Unit;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\TwitchTokenService;
use PHPUnit\Framework\TestCase;
use Mockery;
use Exception;
class TwitchTokenServiceTest extends TestCase
{
    /**
     * @test
     */
    public function givenADatabaseSuccesfulAccesReturnToken()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $apiClientMock = Mockery::mock(ApiClient::class);

        $dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('mocked_token');

        $twitchTokenService = new TwitchTokenService($dbClientMock, $apiClientMock);

        $result = $twitchTokenService->getToken();

        $this->assertEquals('mocked_token', $result);
    }

    /**
     * @test
     */
    public function givenADatabaseFailureGetTokenFromApiAndSaveToDatabase()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $apiClientMock = Mockery::mock(ApiClient::class);

        $dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn(null);

        $dbClientMock
            ->shouldReceive('addTokenToDatabase')
            ->once()
            ->with('api_token');

        $apiClientMock
            ->shouldReceive('getTokenFromAPI')
            ->once()
            ->andReturn('{"access_token": "api_token"}');

        $twitchTokenService = new TwitchTokenService($dbClientMock, $apiClientMock);

        try {
            $result = $twitchTokenService->getToken();
        }
        catch (\Exception $e) {
            $this->fail('Se esperaba que no se lanzara una excepción.');
        }

        $this->assertEquals('api_token', $result);
    }

    /**
     * @test
     */
    public function givenAnApiTokenErrorReturnException()
    {
        $dbClientMock = Mockery::mock(DBClient::class);
        $apiClientMock = Mockery::mock(ApiClient::class);

        $dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn(null);

        $apiClientMock
            ->shouldReceive('getTokenFromAPI')
            ->once()
            ->andThrow(new Exception('API error'));

        $twitchTokenService = new TwitchTokenService($dbClientMock, $apiClientMock);

        try {
            $result = $twitchTokenService->getToken();
        }
        catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
            $this->assertEquals('No se puede establecer conexión con Twitch en este momento', $e->getMessage());
            return;
        }

        $this->fail('Se esperaba que se lanzara una excepción.');
    }
}
