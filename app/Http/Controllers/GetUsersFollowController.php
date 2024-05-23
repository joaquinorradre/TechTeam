<?php

namespace App\Http\Controllers;

use App\Services\UsersFollowDataManager;
use App\Http\Clients\DBClient;
use Exception;
use Illuminate\Http\JsonResponse;

class GetUsersFollowController extends Controller
{
    private DBClient $dbClient;
    private UsersFollowDataManager $userListDataManager;

    public function __construct(UsersFollowDataManager $userListDataManager)
    {
        $this->userListDataManager = $userListDataManager;
    }

    /**s
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        try {
            $usersWithFollowedStreamersData = $this->userListDataManager->getUserData();
            return new JsonResponse($usersWithFollowedStreamersData, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }
    }
}