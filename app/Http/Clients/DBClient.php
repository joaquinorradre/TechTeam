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
                throw new Exception('El usuario ' . $userId . ' especificado no existe en la API', Response::HTTP_NOT_FOUND);
            }
        } catch (Exception $exception) {
            if ($exception->getCode() === Response::HTTP_CONFLICT || $exception->getCode() === Response::HTTP_NOT_FOUND) {
                throw $exception;
            } else {
                throw new Exception('Error del servidor al seguir al streamer', Response::HTTP_INTERNAL_SERVER_ERROR);
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

    private function userExistsInDatabase(string $userId): bool
    {
        return DB::table('users')->where('name', $userId)->exists();
    }

    private function userAlreadyFollowingStreamer(string $userId, string $streamerId): bool
    {
        return DB::table('user_follow')
            ->where('username', $userId)
            ->where('streamerId', $streamerId)
            ->exists();
    }

}
