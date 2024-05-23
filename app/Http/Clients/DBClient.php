<?php

namespace App\Http\Clients;

use Exception;
use Illuminate\Support\Facades\DB;

class DBClient
{
    public static function getConnection()
    {
        try {
            return DB::connection()->getPdo();
        } catch (Exception $exception) {
            die("Error de conexión: " . $$exception->getMessage());
        }
    }

    public function getTokenFromDataBase()
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

    public function getUsersWithFollowedStreamers(): array
    {
        // Recuperar los usuarios y los streamers que siguen
        $users = DB::table('User')
            ->leftJoin('user_follow', 'User.username', '=', 'user_follow.username')
            ->select('User.username', 'user_follow.streamerId')
            ->get();

        return $users->toArray();
    }
}
