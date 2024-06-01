<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use DateTime;

class Methods
{

    public static function fetchTwitchData($url, $clientId, $accessToken)
    {
        $response = Http::withHeaders([
            'Client-ID' => $clientId,
            'Authorization' => 'Bearer ' . $accessToken
        ])->get($url);
        if ($response->ok()) {
            return $response->json();
        }
        return null;
    }




    public static function obtenerTiempoDesdeUltimaActualizacion($idGame)
    {
        $result = DB::table('Game')->select('last_update')->where('game_id', $idGame)->first();

        if ($result) {
            $lastUpdateTimestamp = $result->last_update;
            $lastUpdateTimestamp_dt = new DateTime($lastUpdateTimestamp);
            $tiempoActual = now()->format('Y-m-d H:i:s');
            $tiempoActual_dt = new DateTime($tiempoActual);
            $diff1 = $lastUpdateTimestamp_dt->diff($tiempoActual_dt);
            $hours = $diff1->format('%H');
            $minutes = $diff1->format('%I');
            $seconds = $diff1->format('%S');
            $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
            return $totalSeconds;
        }
        return null;
    }


}