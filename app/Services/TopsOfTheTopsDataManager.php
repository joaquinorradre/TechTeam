<?php

namespace App\Services;

use App\Http\Clients\DBClient;
use App\Utils\Methods;

class TopsOfTheTopsDataManager
{
    private const GAMES_TWITCH_URL = 'https://api.twitch.tv/helix/games/top?first=3';
    private Methods $methods;
    private DBClient $dbClient;

    public function __construct(Methods $methods, DBClient $dbClient)
    {
        $this->methods = $methods;
        $this->dbClient = $dbClient;
    }

    public function fetchGames()
    {
        return $this->dbClient->fetchGames();
    }

    public function updateGamesData()
    {
        $gamesResponse = $this->methods->fetchTwitchData(self::GAMES_TWITCH_URL, $this->getClientId(), $accessToken);
        if ($gamesResponse) {
            $this->methods->insertGames($gamesResponse);
            $this->methods->fetchAndInsertVideos($gamesResponse, $this->getClientId(), $accessToken);
            return $this->dbClient->getGameData();
        }
    }

    public function updateExistingGamesData($accessToken, $request, $since)
    {
        $gamesResponse = $this->methods->fetchTwitchData(self::GAMES_TWITCH_URL, $this->getClientId(), $accessToken);
        if (isset($gamesResponse['data'])) {
            $this->dbClient->deleteObsoleteGames($gamesResponse);
            $this->updateGames($gamesResponse, $request, $since);
            $this->updateVideos($gamesResponse, $this->getClientId(), $accessToken);
            return $this->dbClient->getGameData();
        }
    }

    private function updateGames($gamesResponse, $request, $since)
    {
        $games = array_slice($gamesResponse['data'], 0, 3);
        $existingGameIds = $this->dbClient->fetchGameIds();
        foreach ($games as $game) {
            $existingId = $game['id'];
            $existingGame = $this->dbClient->fetchGameById($existingId);
            if (!$existingGame) {
                $this->dbClient->insertGame($game);
            } else {
                $this->updateExistingGame($gamesResponse, $request, $existingId, $since);
            }
        }
    }

    private function updateExistingGame($gamesResponse, $request, $existingId, $since)
    {
        if ($request->has('since')) {
            $timeDiffInSeconds = $this->methods->obtenerTiempoDesdeUltimaActualizacion($existingId);
            if ($timeDiffInSeconds === null || $timeDiffInSeconds > $since) {
                $this->dbClient->updateGame($existingId, $gamesResponse);
            }
        } else {
            $timeDiffInSeconds = $this->methods->obtenerTiempoDesdeUltimaActualizacion($existingId);
            if ($timeDiffInSeconds === null || $timeDiffInSeconds > 600) { // 600 segundos = 10 minutos
                $this->dbClient->updateGame($existingId, $gamesResponse);
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
}
