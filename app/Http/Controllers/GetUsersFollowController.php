<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetUsersFollowRequest;
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

    /**
     * Handle the incoming request.
     */
    public function __invoke(GetUsersFollowRequest $request): JsonResponse
    {
        try {
            $usersFollowedData = $this->userListDataManager->getUserData();
            return new JsonResponse(
                $usersFollowedData,
                200,
                [],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }
    }
}
