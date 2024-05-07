<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Utils\Methods;
use DateTime;

class GetTopOfTheTops extends Controller
{
    protected $methods;
    protected $database;

    public function __construct(Methods $methods, DB $database)
    {
        $this->methods = $methods;
        $this->database = $database;
    }

    public function fetchData(Request $request)
    {
        $games = $this->fetchGames();
        $gameCount = count($games);
        $gamesTwitchUrl = 'https://api.twitch.tv/helix/games/top?first=3';
        $clientId = 'szp2ugo2j6edjt8ytdak5n2n3hjkq3';
        $clientSecret = '07gk0kbwwzpuw2uqdzy1bjnsz9k32k';
        $accessToken = $this->methods->requestAccessToken($clientId, $clientSecret);

        if ($gameCount == 0 && isset($accessToken)) {
            return $this->updateGamesData($gamesTwitchUrl, $clientId, $accessToken);
        } elseif ($gameCount > 0 && isset($accessToken)) {
            return $this->updateExistingGamesData($gamesTwitchUrl, $clientId, $accessToken, $request);
        }
    }

    private function fetchGames()
    {
        return $this->database::table('Game')->get();
    }

    private function updateGamesData($gamesTwitchUrl, $clientId, $accessToken)
    {
        $gamesResponse = $this->methods->fetchTwitchData($gamesTwitchUrl, $clientId, $accessToken);
        if ($gamesResponse) {
            $this->methods->insertGames($gamesResponse);
            $this->methods->fetchAndInsertVideos($gamesResponse, $clientId, $accessToken);
            $results = $this->getGameData();
            return $this->respondWithData($results);
        }
    }

    private function updateExistingGamesData($gamesTwitchUrl, $clientId, $accessToken, $request)
    {
        $gamesResponse = $this->methods->fetchTwitchData($gamesTwitchUrl, $clientId, $accessToken);
        if (isset($gamesResponse['data'])) {
            $this->deleteObsoleteGames($gamesResponse);
            $this->updateGames($gamesResponse, $request);
            $this->updateVideos($gamesResponse, $clientId, $accessToken);
            $results = $this->getGameData();
            return $this->respondWithData($results);
        }
    }

    private function updateExistingGame($gamesResponse, $request, $existingId)
    {
        if ($request->has('since')) {
            $since = $request->input('since');
            $timeDiffInSeconds = $this->methods->obtenerTiempoDesdeUltimaActualizacion($existingId);
            if ($timeDiffInSeconds === null || $timeDiffInSeconds > $since) {
                $this->updateGame($existingId, $gamesResponse);
            }
        } elseif (!($request->has('since'))) {
            // Verificar si ha pasado más de 10 minutos desde la última actualización
            $timeDiffInSeconds = $this->methods->obtenerTiempoDesdeUltimaActualizacion($existingId);
            if ($timeDiffInSeconds === null || $timeDiffInSeconds > 600) { // 600 segundos = 10 minutos
                $this->updateGame($existingId, $gamesResponse);
            }
        }
    }



    private function updateGames($gamesResponse, $request)
    {
        $games = array_slice($gamesResponse['data'], 0, 3);
        $existingGameIds = $this->database::table('Game')->pluck('game_id')->toArray();
        $newGameIds = array_map(function ($game) {
            return $game['id'];
        }, $games);

        foreach ($games as $game) {
            $existingId = $game['id'];
            $name = $game['name'];
            $existingGame = DB::table('Game')->where('game_id', $existingId)->first();
            if (!$existingGame) {
                DB::table('Game')->insert([
                'game_id' => $game['id'],
                'game_name' => $game['name'],
                'last_update' => now()
                ]);
            } elseif ($existingGame) {
                $this->updateExistingGame($gamesResponse, $request, $existingId);
            }
        }
    }

    private function updateGame($gameId, $gamesResponse)
    {
        $gameToUpdate = null;
        foreach ($gamesResponse['data'] as $game) {
            if ($game['id'] == $gameId) {
                $gameToUpdate = $game;
                break;
            }
        }

        if ($gameToUpdate) {
            $this->database::table('Video')->where('game_id', $gameId)->delete();
            $this->database::table('Game')->where('game_id', $gameId)->delete();

            $this->database::table('Game')->insert([
            'game_id' => $gameToUpdate['id'],
            'game_name' => $gameToUpdate['name'],
            'last_update' => now()
            ]);
        }
    }


    private function deleteObsoleteGames($gamesResponse)
    {
        $games = array_slice($gamesResponse['data'], 0, 3);
        $existingGameIds = $this->database::table('Game')->pluck('game_id')->toArray();
        $newGameIds = array_map(function ($game) {
            return $game['id'];
        }, $games);

        foreach ($existingGameIds as $existingId) {
            if (!in_array($existingId, $newGameIds)) {
                $this->database::table('Video')->where('game_id', $existingId)->delete();
                $this->database::table('Game')->where('game_id', $existingId)->delete();
            }
        }
    }

    private function updateVideos($gamesResponse, $clientId, $accessToken)
    {
        $games = array_slice($gamesResponse['data'], 0, 3);
        foreach ($games as $game) {
            $videosUrl = "https://api.twitch.tv/helix/videos?game_id={$game['id']}&sort=views&first=40";
            $videosResponse = $this->methods->fetchTwitchData($videosUrl, $clientId, $accessToken);
            $this->methods->insertVideos($videosResponse, $game['id']);
        }
    }

    private function getGameData()
    {
        return $this->database::table('Video as v')
            ->select(
                'v.game_id',
                'g.game_name',
                'v.user_name',
                $this->database::raw('total_videos.total_videos AS total_videos'),
                $this->database::raw('total_views.total_views AS total_views'),
                'v.title AS most_viewed_title',
                'v.view_count AS most_viewed_views',
                'v.duration AS most_viewed_duration',
                'v.created_at AS most_viewed_created_at'
            )
            ->join('Game as g', 'v.game_id', '=', 'g.game_id')
            ->join($this->database::raw('(SELECT game_id, MAX(view_count) AS max_view_count FROM Video 
            	GROUP BY game_id) AS max_views_per_game'), function ($join) {
                $join->on('v.game_id', '=', 'max_views_per_game.game_id')
                    ->on('v.view_count', '=', 'max_views_per_game.max_view_count');
            })
            ->join($this->database::raw('(SELECT game_id, user_name, COUNT(*) AS total_videos FROM Video 
                GROUP BY game_id, user_name) AS total_videos'), function ($join) {
                $join->on('v.game_id', '=', 'total_videos.game_id')
                    ->on('v.user_name', '=', 'total_videos.user_name');
            })
            ->join($this->database::raw('(SELECT game_id, user_name, SUM(view_count) AS total_views FROM Video 
                GROUP BY game_id, user_name) AS total_views'), function ($join) {
                $join->on('v.game_id', '=', 'total_views.game_id')
                    ->on('v.user_name', '=', 'total_views.user_name');
            })
            ->get();
    }

    private function respondWithData($results)
    {
        $data = [];
        foreach ($results as $row) {
            $rowData = [
                "game_id" => $row->game_id,
                "game_name" => $row->game_name,
                "user_name" => $row->user_name,
                "total_videos" => strval($row->total_videos),
                "total_views" => $row->total_views,
                "most_viewed_title" => $row->most_viewed_title,
                "most_viewed_views" => strval($row->most_viewed_views),
                "most_viewed_duration" => $row->most_viewed_duration,
                "most_viewed_created_at" => $row->most_viewed_created_at
            ];
            $data[] = $rowData;
        }
        return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
