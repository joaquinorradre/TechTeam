<?php

namespace App\Services;

class GetUsersService
{
    private UserDataManager $userDataManager;

    public function __construct(UserDataManager $userDataManager)
    {
        $this->userDataManager = $userDataManager;
    }

    public function execute($userId)
    {
        $userData = $this->userDataManager->getUserData($userId);
        $response = json_decode($userData, true);

        if ($response === null) {
            return [];
        }

        return($response['data']);
    }

}