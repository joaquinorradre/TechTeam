<?php

namespace App\Http\Controllers;
use App\Services\UserListDataManager;
use Illuminate\Http\JsonResponse;

class GetUsersListController extends Controller
{
    private UserListDataManager $userListDataManager;

    public function __construct(UserListDataManager $userListDataManager)
    {
        $this->userListDataManager = $userListDataManager;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        try {
            // Obtener los datos de usuarios y streamers
            $userData = $this->userListDataManager->getUserData();

            return new JsonResponse($userData, 200);
        }
        catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }
    }
}