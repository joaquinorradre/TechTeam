<?php

namespace App\Http\Controllers;

use App\Services\UserDataManager;
use Illuminate\Http\Request;

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
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = $request->input('id');
        if (!$userId) {
            return response()->json(['error' => 'Se requiere el parámetro "id" en la URL'], 400);
        }

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
