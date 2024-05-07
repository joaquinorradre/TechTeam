<?php

namespace App\Clients;

use Illuminate\Support\Facades\DB;

class DBClient
{
    public static function getConnection()
    {
        try {
            $pdo = DB::connection()->getPdo();
            return $pdo;
        } catch (\Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getTokenFromDataBase()
    {
        $token = DB::table('Token')->value('token');

        return $token ? $token : null;
    }


    public function addTokenToDataBase(string $token): void
    {
        $currentTime = now(); // Use Carbon instance for the current time

        DB::table('Token')->insert([
            'token' => $token,
            'created_at' => $currentTime,
            'updated_at' => $currentTime,
        ]);
    }

}
