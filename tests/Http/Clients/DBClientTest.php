<?php

namespace Tests\Unit\Http\Clients;

use App\Http\Clients\DBClient;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DBClientTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->dbClient = new DBClient();
    }

    /** @test */
    public function getTokenFromDatabase()
    {
        $token = 'test_token';
        DB::table('Token')->insert(['token' => $token, 'created_at' => now(), 'updated_at' => now()]);

        $result = $this->dbClient->getTokenFromDatabase();

        $this->assertEquals($token, $result);
    }

    /** @test */
    public function addTokenToDatabase()
    {
        $token = 'test_token';

        $this->dbClient->addTokenToDatabase($token);

        $tokenFromDB = DB::table('Token')->where('token', $token)->first();

        $this->assertNotNull($tokenFromDB);
        $this->assertEquals($token, $tokenFromDB->token);
        $this->assertNotNull($tokenFromDB->created_at);
        $this->assertNotNull($tokenFromDB->updated_at);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function addStreamerToDatabase()
    {
        $userId = 'user_id_de_prueba';
        $streamerId = 'streamer_id_de_prueba';

        $this->assertTrue($this->dbClient->userExistsInDatabase($userId));

        $this->dbClient->addStreamerToDatabase($userId, $streamerId);

        $this->assertTrue($this->dbClient->userAlreadyFollowingStreamer($userId, $streamerId));
    }

    /** @test */
    public function getUsersWithFollowedStreamers()
    {
        $usersWithFollowedStreamers = $this->dbClient->getUsersWithFollowedStreamers();

        $this->assertIsArray($usersWithFollowedStreamers);
    }

    /** @test
     * @throws \Exception
     */
    public function getFollowedStreamers()
    {
        $userId = 'user_id_de_prueba';

        $this->assertTrue($this->dbClient->userExistsInDatabase($userId));

        $followedStreamers = $this->dbClient->getFollowedStreamers($userId);

        $this->assertIsArray($followedStreamers);
    }

    /** @test */
    public function fetchGames()
    {
        $games = $this->dbClient->fetchGames();

        $this->assertIsArray($games);
    }

    /** @test */
    public function fetchGameById()
    {
        $gameId = 'game_id_de_prueba';

        $game = $this->dbClient->fetchGameById($gameId);

        $this->assertNotNull($game);
    }

    /** @test */
    public function insertGame()
    {
        $game = [
            'id' => 'game_id_de_prueba',
            'name' => 'Nombre del Juego de Prueba'
        ];

        $this->dbClient->insertGame($game);

        $this->assertTrue(DB::table('Game')->where('game_id', $game['id'])->exists());
    }

    /** @test */
    public function updateGame()
    {
        $gameId = 'game_id_de_prueba';
        $gamesResponse = [
            'data' => [
                ['id' => 'game_id_de_prueba', 'name' => 'Nuevo Nombre del Juego de Prueba']
            ]
        ];

        $this->dbClient->updateGame($gameId, $gamesResponse);

        $this->assertEquals('Nuevo Nombre del Juego de Prueba', DB::table('Game')->where('game_id', $gameId)->value('game_name'));
    }

    /** @test */
    public function deleteObsoleteGames()
    {
        $gamesResponse = [
            'data' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
                ['id' => 4],
                ['id' => 5],
            ]
        ];

        DB::table('Game')->insert([
            ['game_id' => 1],
            ['game_id' => 2],
            ['game_id' => 3],
        ]);

        foreach (range(1, 3) as $gameId) {
            DB::table('Video')->insert([
                ['game_id' => $gameId, 'title' => 'Video 1'],
                ['game_id' => $gameId, 'title' => 'Video 2'],
            ]);
        }

        $this->dbClient->deleteObsoleteGames($gamesResponse);
        $this->assertEquals(0, DB::table('Game')->whereIn('game_id', [4, 5])->count());
        $this->assertEquals(0, DB::table('Video')->whereIn('game_id', [4, 5])->count());
    }

    /** @test */
    public function deleteStreamerFromDatabase()
    {
        $userId = 'user_id_de_prueba';
        $streamerId = 'streamer_id_de_prueba';

        $this->dbClient->deleteStreamerFromDatabase($userId, $streamerId);

        $this->assertFalse($this->dbClient->userAlreadyFollowingStreamer($userId, $streamerId));
    }

    /** @test */
    public function userAlreadyFollowingStreamer()
    {
        $userId = 'user_id_de_prueba';
        $streamerId = 'streamer_id_de_prueba';

        DB::table('user_follow')->insert([
            'username' => $userId,
            'streamerId' => $streamerId,
        ]);

        $this->assertTrue($this->dbClient->userAlreadyFollowingStreamer($userId, $streamerId));
    }

    /** @test */
    public function getGameData()
    {
        $gameData = $this->dbClient->getGameData();

        $this->assertNotEmpty($gameData);
    }

    /** @test */
    public function createUser()
    {
        $username = 'usuario_de_prueba';
        $password = 'contraseña_de_prueba';

        $this->dbClient->createUser($username, $password);

        $this->assertTrue($this->dbClient->userExistsInDatabase($username));
    }

    /** @test */
    public function userExistsInDatabase()
    {
        $existingUsername = 'usuario_existente';

        $this->assertTrue($this->dbClient->userExistsInDatabase($existingUsername));

        $nonExistingUsername = 'usuario_inexistente';

        $this->assertFalse($this->dbClient->userExistsInDatabase($nonExistingUsername));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

