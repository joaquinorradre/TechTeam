<?php

namespace App\Services;

use Exception;

class GetTopOfTheTopsService
{
    private TopsOfTheTopsDataManager $topsOfTheTopsDataManager;

    public function __construct(TopsOfTheTopsDataManager $topsOfTheTopsDataManager)
    {
        $this->topsOfTheTopsDataManager = $topsOfTheTopsDataManager;
    }

    /**
     * @throws Exception
     */
    public function execute($since)
    {
        $gameCount = count($this->topsOfTheTopsDataManager->fetchGames());
        if ($gameCount == 0) {
            return $this->topsOfTheTopsDataManager->updateGamesData();
        } elseif ($gameCount > 0) {
            return $this->topsOfTheTopsDataManager->updateExistingGamesData($since);
        }
    }
}
