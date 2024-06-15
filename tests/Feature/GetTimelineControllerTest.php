<?php

namespace Tests\Feature;

use App\Http\Clients\DBClient;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\TestCase;

class GetTimelineControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = new DBClient();
        $dbClient->createUser('user123', Hash::make('password123'));
        $dbClient->addStreamerToDatabase('user123', 'streamer123');
    }
    /**
     * @test
     */
    public function getsTimeline()
    {
        $response = $this->get('/analytics/timeline', ['userId' => 'user123']);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            '*' => [
                'streamerId',
                'userName',
                'title',
                'gameName',
                'viewerCount',
                'startedAt'
            ]
        ]);
    }

    /**
     * @test
     */
    public function doesNotGetTimelineIfUserDoesNotExist()
    {
        $response = $this->get('/analytics/timeline', ['userId' => 'user456']);

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $response->assertJson([
            'error' => 'El usuario especificado user456 no existe.'
        ]);
    }

    /**
     * @test
     * @throws Exception
     */
    public function doesNotGetTimelineIfServerError()
    {
        $response = $this->get('/analytics/timeline', ['userId' => 'user123', 'forceError' => true]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        $response->assertJson([
            'error' => 'Error del servidor al obtener el timeline.'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}