<?php

namespace App\Services;

use App\Http\Clients\DBClient;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class DeleteStreamerService
{
    private DBClient $dbClient;
    public function __construct(DBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    public function execute(string $userId, string $streamerId): bool
    {
        $deletedRows = $this->dbClient->deleteStreamerFromDatabase($userId, $streamerId);
        return $deletedRows > 0;
    }
}