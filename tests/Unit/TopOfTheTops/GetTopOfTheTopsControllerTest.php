<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\GetTopOfTheTopsController;
use App\Serializers\TopsOfTheTopsDataSerializer;
use App\Services\GetTopOfTheTopsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetTopOfTheTopsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getTopOfTheTopsService = Mockery::mock(GetTopOfTheTopsService::class);
        $this->topsOfTheTopsDataSerializer = Mockery::mock(TopsOfTheTopsDataSerializer::class);
        $this->controller = new GetTopOfTheTopsController($this->getTopOfTheTopsService, $this->topsOfTheTopsDataSerializer);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function GetTopOfTheTopsDataTest()
    {
        // Arrange
        $since = 'sinceVal';
        $request = Request::create('/top-of-the-tops', 'GET', ['since' => $since]);
        $expectedData = [
            'data' => [
                ['id' => '509658', 'name' => 'Just Chatting'],
                ['id' => '516575', 'name' => 'VALORANT'],
                ['id' => '263490', 'name' => 'Rust']
            ]
        ];

        $this->getTopOfTheTopsService
            ->shouldReceive('execute')
            ->once()
            ->with($since)
            ->andReturn($expectedData);

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            json_encode($expectedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $response->getContent()
        );
    }
}
