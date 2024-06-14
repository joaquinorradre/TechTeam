<?php

namespace App\Services;

use App\Http\Clients\DBClient;
use Exception;

class PostStreamerService
{
    private StreamerExistManager $streamerExistManager;
    private DBClient $dbClient;
    public function __construct(StreamerExistManager $streamerExistManager,DBClient $dbClient)
    {
        $this->streamerExistManager = $streamerExistManager;
        $this->dbClient = $dbClient;
    }

    public function execute(string $userId, string $streamerId): void
    {
        try {
            $streamerExists = $this->streamerExistManager->getStreamer($streamerId);
            if ($streamerExists) {
                $this->dbClient->addStreamerToDatabase($userId, $streamerId);
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}