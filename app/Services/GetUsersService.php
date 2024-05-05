<?php

namespace App\Services;

class GetUsersService
{
    private UsersManager $userManager;

    public function __construct(UsersManager $usersManager)
    {
        $this->userManager = $usersManager;
    }

    public function getUserInfo($userId)
    {
        $url = "https://api.twitch.tv/helix/users?id=$userId";

        $users = $this->userManager->getUsers($url);

        $response = json_decode($users, true);

        $filtered_users = [];

        if (isset($response['data'])) {
            foreach ($response['data'] as $user) {
                $user_data = [];

                foreach ($user as $key => $value) {
                    $user_data[$key] = $value;
                }
                $filtered_users[] = $user_data;
            }
        }

        return $filtered_users;

    }

}