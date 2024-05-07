<?php

namespace App\Http\Controllers;

use App\Services\UserDataManager;
use App\Http\Requests\GetUsersRequest; // Importar el Form Request
use Illuminate\Http\JsonResponse;

class GetUsers extends Controller
{
    private UserDataManager $userDataManager;

    public function __construct(UserDataManager $userDataManager)
    {
        $this->userDataManager = $userDataManager;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(GetUsersRequest $request): JsonResponse
    {
        $userId = $request->input('id');

        $url = "https://api.twitch.tv/helix/users?id=$userId";

        $usersData = $this->userDataManager->getUserData($url);

        $response = json_decode($usersData, true);

        $filteredUsers = [];
        if (isset($response['data'])) {
            foreach ($response['data'] as $user) {
                $userData = [];
                foreach ($user as $key => $value) {
                    $userData[$key] = $value;
                }
                $filteredUsers[] = $userData;
            }
        }

        return response()->json($filteredUsers, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
