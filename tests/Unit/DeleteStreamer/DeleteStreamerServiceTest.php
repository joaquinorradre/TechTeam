<?php

namespace Tests\Unit\Services;

use App\Http\Clients\DBClient;
use App\Services\DeleteStreamerService;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
class DeleteStreamerServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function executeMethodWhenDeletedRows()
    {
        $dbClient = Mockery::mock(DBClient::class);
        $dbClient
            ->shouldReceive('deleteStreamerFromDatabase')
            ->once()
            ->with('user123', 'streamer123')
            ->andReturn(1);
        $service = new DeleteStreamerService($dbClient);

        $result = $service->execute('user123', 'streamer123');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function executeMethodWhenNoDeletedRows()
    {
        $dbClient = Mockery::mock(DBClient::class);
        $dbClient->shouldReceive('deleteStreamerFromDatabase')
            ->once()
            ->with('user123', 'streamer123')
            ->andThrow(new Exception('El usuario user123 o el streamer streamer123 especificado no existe en la API', Response::HTTP_NOT_FOUND));
        $service = new DeleteStreamerService($dbClient);

        try {
            $service->execute('user123', 'streamer123');
        }
        catch (\Exception $result) {
            $this->assertEquals('El usuario user123 o el streamer streamer123 especificado no existe en la API', $result->getMessage());
            $this->assertEquals(Response::HTTP_NOT_FOUND, $result->getCode());
            return;
        }
        $this->fail('Se esperaba que se lanzara una excepción.');
    }
}
