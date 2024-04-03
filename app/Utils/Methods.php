<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use DateTime;

class Methods
{
    public static function requestAccessToken($clientId, $clientSecret)
    {
        $accessTokenUrl = "https://id.twitch.tv/oauth2/token?" .
        "client_id={$clientId}&" .
        "client_secret={$clientSecret}&" .
        "grant_type=client_credentials";

        $response = Http::post($accessTokenUrl);
        if ($response->ok()) {
            return $response['access_token'];
        }
        return null;
    }

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

    public static function insertGames($gamesResponse)
    {
        foreach ($gamesResponse['data'] as $game) {
            $existingGame = DB::table('Game')->where('game_id', $game['id'])->first();
            DB::table('Game')->insert([
                'game_id' => $game['id'],
                'game_name' => $game['name'],
                'last_update' => now()
            ]);
        }
    }

    public static function fetchAndInsertVideos($gamesResponse, $clientId, $accessToken)
    {
        foreach ($gamesResponse['data'] as $game) {
            $videosUrl = "https://api.twitch.tv/helix/videos?game_id={$game['id']}&sort=views&first=40";

            $videosResponse = fetchTwitchData($videosUrl, $clientId, $accessToken);

            if ($videosResponse) {
                insertVideos($videosResponse, $game['id']);
            }
        }
    }

    public static function insertVideos($videosResponse, $idGame)
    {
        // Insertar los videos en la base de datos
        foreach ($videosResponse['data'] as $video) {
        // Verificar si el video ya existe en la base de datos antes de insertarlo
            $existingVideo = DB::table('Video')->where('id', $video['id'])->first();

            DB::table('Video')->insert([
                'id' => $video['id'],
                'user_id' => $video['user_id'],
                'user_name' => $video['user_name'],
                'title' => $video['title'],
                'created_at' => $video['created_at'],
                'view_count' => $video['view_count'],
                'duration' => $video['duration'],
                'game_id' => $idGame
            ]);
        }
    }

    public static function conexion()
    {
        try {
            $connection = DB::connection()->getPdo();
            return $connection;
        } catch (\Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function obtenerTiempoDesdeUltimaActualizacion($idGame)
    {
        $result = DB::table('Game')->select('last_update')->where('game_id', $idGame)->first();

        if ($result) {
            $lastUpdateTimestamp = $result->last_update;
            $UpdateTimestamp_dt = new DateTime($lastUpdateTimestamp);
            $tiempoActual = now()->format('Y-m-d H:i:s');
            $tiempoActual_dt = new DateTime($tiempoActual);
            $diff1 = $UpdateTimestamp_dt->diff($tiempoActual_dt);
            $hours = $diff1->format('%H');
            $minutes = $diff1->format('%I');
            $seconds = $diff1->format('%S');
            $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
            return $totalSeconds;
        }
        return null;
    }
    public static function masTiempoMax($gameId, $accessToken)
    {
        DB::table('Video')->where('game_id', $gameId)->delete();
        DB::table('Game')->where('game_id', $gameId)->update(['last_update' => now()]);
        $videosTwitchUrl = "https://api.twitch.tv/helix/videos?game_id={$gameId}&sort=views&first=40";
        $headers = [
        'Client-ID' => 'szp2ugo2j6edjt8ytdak5n2n3hjkq3',
        'Authorization' => 'Bearer ' . $accessToken
        ];

        $videosTwitchResponse = Http::withHeaders($headers)->get($videosTwitchUrl);
        if ($videosTwitchResponse->failed()) {
            abort(500, 'Error al obtener los datos de los videos');
        }

        $videosTwitchData = $videosTwitchResponse->json();
        if (isset($videosTwitchData['data'])) {
            foreach ($videosTwitchData['data'] as $video) {
                DB::table('Video')->insert([
                'id' => $video['id'],
                'user_id' => $video['user_id'],
                'user_name' => $video['user_name'],
                'title' => $video['title'],
                'created_at' => $video['created_at'],
                'view_count' => $video['view_count'],
                'duration' => $video['duration'],
                'game_id' => $gameId
                ]);
            }
        }
        $result = (object) DB::table('Video as v')
        ->select(
            'v.game_id',
            'g.game_name',
            'v.user_name',
            'total_videos.total_videos AS total_videos',
            'total_views.total_views AS total_views',
            'v.title AS most_viewed_title',
            'v.view_count AS most_viewed_views',
            'v.duration AS most_viewed_duration',
            'v.created_at AS most_viewed_created_at'
        )
        ->join('Game as g', 'v.game_id', '=', 'g.game_id')
        ->join(
            DB::raw('(SELECT game_id, MAX(view_count) AS max_view_count FROM Video 
		    GROUP BY game_id) AS max_views_per_game'),
            function ($join) {
                $join->on('v.game_id', '=', 'max_views_per_game.game_id')
                ->on('v.view_count', '=', 'max_views_per_game.max_view_count');
            }
        )
        ->join(
            DB::raw('(SELECT game_id, user_name, COUNT(*) AS total_videos FROM Video 
		    GROUP BY game_id, user_name) AS total_videos'),
            function ($join) {
                $join->on('v.game_id', '=', 'total_videos.game_id')
                ->on('v.user_name', '=', 'total_videos.user_name');
            }
        )
        ->join(
            DB::raw('(SELECT game_id, user_name, SUM(view_count) AS total_views FROM Video 
		    GROUP BY game_id, user_name) AS total_views'),
            function ($join) {
                $join->on('v.game_id', '=', 'total_views.game_id')
                ->on('v.user_name', '=', 'total_views.user_name');
            }
        )
        ->where('v.game_id', $gameId)
        ->get();

        if (empty($result)) {
            return response()->json(["message" => "No se encontraron datos para el juego con ID $gameId."], 404);
        }

        $formattedResult = (object)[
        'game_id' => $result[0]->game_id,
        'game_name' => $result[0]->game_name,
        'user_name' => $result[0]->user_name,
        'total_videos' => strval($result[0]->total_videos),
        'total_views' => $result[0]->total_views,
        'most_viewed_title' => $result[0]->most_viewed_title,
        'most_viewed_views' => strval($result[0]->most_viewed_views),
        'most_viewed_duration' => $result[0]->most_viewed_duration,
        'most_viewed_created_at' => $result[0]->most_viewed_created_at
        ];

        return $formattedResult;
    }

    public static function menosTiempoMax($game_id)
    {
        $result = (object) DB::table('Video as v')
        ->select(
            'v.game_id',
            'g.game_name',
            'v.user_name',
            'total_videos.total_videos AS total_videos',
            'total_views.total_views AS total_views',
            'v.title AS most_viewed_title',
            'v.view_count AS most_viewed_views',
            'v.duration AS most_viewed_duration',
            'v.created_at AS most_viewed_created_at'
        )
        ->join('Game as g', 'v.game_id', '=', 'g.game_id')
        ->join(
            DB::raw('(SELECT game_id, MAX(view_count) AS max_view_count FROM Video 
		    GROUP BY game_id) AS max_views_per_game'),
            function ($join) {
                $join->on('v.game_id', '=', 'max_views_per_game.game_id')
                ->on('v.view_count', '=', 'max_views_per_game.max_view_count');
            }
        )
        ->join(
            DB::raw('(SELECT game_id, user_name, COUNT(*) AS total_videos FROM Video 
		    GROUP BY game_id, user_name) AS total_videos'),
            function ($join) {
                $join->on('v.game_id', '=', 'total_videos.game_id')
                ->on('v.user_name', '=', 'total_videos.user_name');
            }
        )
        ->join(
            DB::raw('(SELECT game_id, user_name, SUM(view_count) AS total_views FROM Video 
		    GROUP BY game_id, user_name) AS total_views'),
            function ($join) {
                $join->on('v.game_id', '=', 'total_views.game_id')
                ->on('v.user_name', '=', 'total_views.user_name');
            }
        )
        ->where('v.game_id', $game_id)
        ->get();

        if (empty($result)) {
            return response()->json(["message" => "No se encontraron datos para el juego con ID $game_id."], 404);
        }

        $formattedResult = (object)[
        'game_id' => $result[0]->game_id,
        'game_name' => $result[0]->game_name,
        'user_name' => $result[0]->user_name,
        'total_videos' => strval($result[0]->total_videos),
        'total_views' => $result[0]->total_views,
        'most_viewed_title' => $result[0]->most_viewed_title,
        'most_viewed_views' => strval($result[0]->most_viewed_views),
        'most_viewed_duration' => $result[0]->most_viewed_duration,
        'most_viewed_created_at' => $result[0]->most_viewed_created_at
        ];

        return $formattedResult;
    }
}
