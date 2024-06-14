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
    private $dbClientMock;
    private $apiClientMock;
    private $twitchTokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbClientMock = Mockery::mock(DBClient::class);
        $this->apiClientMock = Mockery::mock(ApiClient::class);

        $this->twitchTokenService = new TwitchTokenService($this->dbClientMock, $this->apiClientMock);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_returns_token_when_database_access_is_successful()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn('mocked_token');

        $result = $this->twitchTokenService->getToken();

        $this->assertEquals('mocked_token', $result);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_fetches_token_from_api_and_saves_to_database_when_database_access_fails()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn(null);
        $this->dbClientMock
            ->shouldReceive('addTokenToDatabase')
            ->once()
            ->with('api_token');
        $this->apiClientMock
            ->shouldReceive('getTokenFromAPI')
            ->once()
            ->andReturn('{"access_token": "api_token"}');

        $result = $this->twitchTokenService->getToken();

        $this->assertEquals('api_token', $result);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_api_token_retrieval_fails()
    {
        $this->dbClientMock
            ->shouldReceive('getTokenFromDatabase')
            ->once()
            ->andReturn(null);
        $this->apiClientMock
            ->shouldReceive('getTokenFromAPI')
            ->once()
            ->andThrow(new Exception('API error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token de autenticación no proporcionado o inválido');

        $this->twitchTokenService->getToken();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}