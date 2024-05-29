<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use App\Services\Methods;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TopsOfTheTopsDataManager
{
    private const GAMES_TWITCH_URL = 'https://api.twitch.tv/helix/games/top?first=3';
    private Methods $methods;
    private DBClient $dbClient;
    private ApiClient $apiClient;
    private TwitchTokenService $twitchTokenService;


    public function __construct(Methods $methods, DBClient $dbClient, ApiClient $apiClient, TwitchTokenService $twitchTokenService)
    {
        $this->methods = $methods;
        $this->dbClient = $dbClient;
        $this->apiClient = $apiClient;
        $this->twitchTokenService = $twitchTokenService;
    }

    public function fetchGames(): \Illuminate\Support\Collection
    {
        return $this->dbClient->fetchGames();
    }

    /**
     * @throws Exception
     */
    public function updateGamesData()
    {
        try{
            $accessToken = $this->twitchTokenService->getToken();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        try{
            $gamesResult = $this->apiClient->makeCurlCall(self::GAMES_TWITCH_URL, $accessToken);
            $gamesResponse = $gamesResult['response'];
            $statusCode = $gamesResult['status'];
            $gamesResponse = json_decode($gamesResponse, true);
            if ($gamesResponse) {
                $this->insertGames($gamesResponse);
                $this->fetchAndInsertVideos($gamesResponse, $accessToken);
                return $this->dbClient->getGameData();
            }

            if ($statusCode == Response::HTTP_INTERNAL_SERVER_ERROR) {
                throw new Exception('No se pueden devolver juegos en este momento, inténtalo más tarde', Response::HTTP_SERVICE_UNAVAILABLE);
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function updateExistingGamesData($since)
    {
        try{
            $accessToken = $this->twitchTokenService->getToken();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        $gamesResult = $this->apiClient->makeCurlCall(self::GAMES_TWITCH_URL, $accessToken);
        $gamesResponse = $gamesResult['response'];
        $statusCode = $gamesResult['status'];
        $gamesResponse = json_decode($gamesResponse, true);
        if (isset($gamesResponse['data'])) {
            $this->dbClient->deleteObsoleteGames($gamesResponse);
            $this->updateGames($gamesResponse, $since);
            $this->updateVideos($gamesResponse, $accessToken);
            return $this->dbClient->getGameData();
        }
    }

    private function updateGames($gamesResponse, $since): void
    {
        $games = array_slice($gamesResponse['data'], 0, 3);
        foreach ($games as $game) {
            $existingId = $game['id'];
            $existingGame = $this->dbClient->fetchGameById($existingId);
            if (!$existingGame) {
                $this->dbClient->insertGame($game);
            } else {
                $this->updateExistingGame($gamesResponse, $existingId, $since);
            }
        }
    }

    private function updateExistingGame($gamesResponse, $existingId, $since): void
    {
        $timeDiffInSeconds = $this->methods->obtenerTiempoDesdeUltimaActualizacion($existingId);
        if ($timeDiffInSeconds === null || $timeDiffInSeconds > $since) {
            $this->dbClient->updateGame($existingId, $gamesResponse);
        }

    }

    /**
     * @throws Exception
     */
    private function updateVideos($gamesResponse, $accessToken): void
    {
        $games = array_slice($gamesResponse['data'], 0, 3);
        foreach ($games as $game) {
            $videosUrl = "https://api.twitch.tv/helix/videos?game_id={$game['id']}&sort=views&first=40";
            $videosResult = $this->apiClient->makeCurlCall($videosUrl, $accessToken);
            $videosResponse = $videosResult['response'];
            $videosResponse = json_decode($videosResponse,true);
            $this->insertVideos($videosResponse, $game['id']);
        }
    }

    public static function insertGames($gamesResponse): void
    {
        foreach ($gamesResponse['data'] as $game) {
            $existingGame = DB::table('Game')->where('game_id', $game['id'])->first();
            if (!$existingGame) {
                DB::table('Game')->insert([
                    'game_id' => $game['id'],
                    'game_name' => $game['name'],
                    'last_update' => now()
                ]);
            }
        }
    }

    public function fetchAndInsertVideos($gamesResponse, $accessToken): void
    {
        try {
            foreach ($gamesResponse['data'] as $game) {
                $videosUrl = "https://api.twitch.tv/helix/videos?game_id={$game['id']}&sort=views&first=40";

                $videosResult = $this->apiClient->makeCurlCall($videosUrl, $accessToken);
                $videosResponse = $videosResult['response'];
                $videosResponse = json_decode($videosResponse, true);
                if ($videosResponse) {
                    self::insertVideos($videosResponse, $game['id']);
                }else{
                    throw new Exception('No se pueden devolver streams en este momento, inténtalo más tarde', Response::HTTP_SERVICE_UNAVAILABLE);
                }
            }
        }catch (Exception $exception) {
            if($exception == Response::HTTP_SERVICE_UNAVAILABLE){
                throw $exception;
            }
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public static function insertVideos($videosResponse, $idGame): void
    {
        try {
            foreach ($videosResponse['data'] as $video) {
                $existingVideo = DB::table('Video')->where('id', $video['id'])->first();
                if (!$existingVideo) {
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
        }catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

}
