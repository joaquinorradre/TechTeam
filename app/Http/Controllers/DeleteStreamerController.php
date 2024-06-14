<?php

namespace App\Http\Controllers;

use App\Http\Clients\DBClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteStreamerController extends Controller
{
    private DBClient $dbClient;

    public function __construct(DBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->input('userId');
        $streamerId = $request->input('streamerId');
        try {
            $this->dbClient->deleteStreamerFromDatabase($userId, $streamerId);
            $response = ["message" => "Has dejado de seguir a $streamerId"];
            return new JsonResponse($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $exception) {
            $response = ["message" => $exception->getMessage()];
            return new JsonResponse($response, $exception->getCode(), [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }
}
