<?php

namespace App\Http\Clients;

use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
class DBClient
{
    public static function getConnection()
    {
        try {
            return DB::connection()->getPdo();
        } catch (Exception $exception) {
            die("Error de conexión: " . $exception->getMessage());
        }
    }

    public function getTokenFromDatabase()
    {
        $token = DB::table('Token')->value('token');

        return $token ? $token : null;
    }

    public function addTokenToDatabase(string $token): void
    {
        $currentTime = now();

        DB::table('Token')->insert([
            'token' => $token,
            'created_at' => $currentTime,
            'updated_at' => $currentTime,
        ]);
    }

    /**
     * @throws Exception
     */
    public function addStreamerToDatabase(string $userId, string $streamerId): void
    {
        try {
            if ($this->userExistsInDatabase($userId)) {
                if (!$this->userAlreadyFollowingStreamer($userId, $streamerId)) {
                    DB::table('user_follow')->insert([
                        'username' => $userId,
                        'streamerId' => $streamerId,
                    ]);
                } else {
                    throw new Exception('El usuario ya está siguiendo al streamer', Response::HTTP_CONFLICT);
                }
            } else {
                throw new Exception('El usuario ' . $userId . ' especificado no existe en la BBDD', Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $exception) {
            if ($exception->getCode() === Response::HTTP_CONFLICT || $exception->getCode() === Response::HTTP_NOT_FOUND) {
                throw $exception;
            } else {
                throw new Exception('Error del servidor al seguir al streamer', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function getUsersWithFollowedStreamers(): array
    {
        $usersWithFollowedStreamers = DB::table('User')
            ->leftJoin('user_follow', 'User.username', '=', 'user_follow.username')
            ->select('User.username', 'user_follow.streamerId')
            ->get();

        return $usersWithFollowedStreamers->toArray();
    }

    /**
     * @throws Exception
     */
    public function getFollowedStreamers(string $userId): array
    {
        if ($this->userExistsInDatabase($userId)) {
            return DB::table('user_follow')
                ->where('username', $userId)
                ->select('streamerId')
                ->get()
                ->toArray();
        } else {
            throw new Exception('El usuario especificado ' . $userId . ' no existe', Response::HTTP_NOT_FOUND);
        }
    }
        
    public function fetchGames()
    {
        return DB::table('Game')->get();
    }

    public function fetchGameIds()
    {
        return DB::table('Game')->pluck('game_id')->toArray();
    }

    public function fetchGameById($gameId)
    {
        return DB::table('Game')->where('game_id', $gameId)->first();
    }

    public function insertGame($game)
    {
        DB::table('Game')->insert([
            'game_id' => $game['id'],
            'game_name' => $game['name'],
            'last_update' => now()
        ]);
    }

    public function updateGame($gameId, $gamesResponse)
    {
        $gameToUpdate = collect($gamesResponse['data'])->firstWhere('id', $gameId);
        if ($gameToUpdate) {
            DB::table('Video')->where('game_id', $gameId)->delete();
            DB::table('Game')->where('game_id', $gameId)->delete();

            DB::table('Game')->insert([
                'game_id' => $gameToUpdate['id'],
                'game_name' => $gameToUpdate['name'],
                'last_update' => now()
            ]);
        }
    }

    public function deleteObsoleteGames($gamesResponse)
    {
        $games = array_slice($gamesResponse['data'], 0, 3);
        $existingGameIds = DB::table('Game')->pluck('game_id')->toArray();
        $newGameIds = array_column($games, 'id');

        foreach ($existingGameIds as $existingId) {
            if (!in_array($existingId, $newGameIds)) {
                DB::table('Video')->where('game_id', $existingId)->delete();
                DB::table('Game')->where('game_id', $existingId)->delete();

            }
        }
    }

    public function deleteStreamerFromDatabase(string $userId, string $streamerId): int
    {
        try {
            $deletedRows = DB::table('user_follow')
                ->where('username', $userId)
                ->where('streamerId', $streamerId)
                ->delete();

            if ($deletedRows === 0) {
                throw new Exception('El usuario ' . $userId . ' o el streamer ' . $streamerId . ' especificado no existe en la API', Response::HTTP_NOT_FOUND);
            }

            return $deletedRows;
        } catch (Exception $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                throw $exception;
            }
            throw new Exception('Error del servidor al dejar de seguir al streamer', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function userAlreadyFollowingStreamer(string $userId, string $streamerId): bool
    {
        return DB::table('user_follow')
            ->where('username', $userId)
            ->where('streamerId', $streamerId)
            ->exists();
    }

    public function getGameData()
    {
        return DB::table('Video as v')
            ->select(
                'v.game_id',
                'g.game_name',
                'v.user_name',
                DB::raw('total_videos.total_videos AS total_videos'),
                DB::raw('total_views.total_views AS total_views'),
                'v.title AS most_viewed_title',
                'v.view_count AS most_viewed_views',
                'v.duration AS most_viewed_duration',
                'v.created_at AS most_viewed_created_at'
            )
            ->join('Game as g', 'v.game_id', '=', 'g.game_id')
            ->join(DB::raw('(SELECT game_id, MAX(view_count) AS max_view_count FROM Video 
                GROUP BY game_id) AS max_views_per_game'), function ($join) {
                $join->on('v.game_id', '=', 'max_views_per_game.game_id')
                    ->on('v.view_count', '=', 'max_views_per_game.max_view_count');
            })
            ->join(DB::raw('(SELECT game_id, user_name, COUNT(*) AS total_videos FROM Video 
                GROUP BY game_id, user_name) AS total_videos'), function ($join) {
                $join->on('v.game_id', '=', 'total_videos.game_id')
                    ->on('v.user_name', '=', 'total_videos.user_name');
            })
            ->join(DB::raw('(SELECT game_id, user_name, SUM(view_count) AS total_views FROM Video 
                GROUP BY game_id, user_name) AS total_views'), function ($join) {
                $join->on('v.game_id', '=', 'total_views.game_id')
                    ->on('v.user_name', '=', 'total_views.user_name');
            })
            ->get();
    }

    /**
     * @throws Exception
     */
    public function createUser(string $username, string $password): void
    {
        try {
            DB::table('User')->insert([
                'username' => $username,
                'password' => $password,
            ]);
        } catch (Exception $exception) {
            throw new Exception('Error del servidor al crear el usuario', 500);
        }
    }

    public function userExistsInDatabase(string $username): bool
    {
        return DB::table('User')->where('username', $username)->exists();
    }

}

