<?php

namespace App\Services;

use App\Services\TopsOfTheTopsDataManager;
use Illuminate\Http\Request;

class GetTopOfTheTopsService
{
    private TopsOfTheTopsDataManager $topsOfTheTopsDataManager;

    public function __construct(TopsOfTheTopsDataManager $topsOfTheTopsDataManager)
    {
        $this->topsOfTheTopsDataManager = $topsOfTheTopsDataManager;
    }

    public function execute(Request $request, $since)
    {
        $games = $this->topsOfTheTopsDataManager->fetchGames();
        $gameCount = count($games);

        if ($gameCount == 0) {
            return $this->topsOfTheTopsDataManager->updateGamesData();
        } elseif ($gameCount > 0) {
            return $this->topsOfTheTopsDataManager->updateExistingGamesData($request, $since);
        }
    }
}
