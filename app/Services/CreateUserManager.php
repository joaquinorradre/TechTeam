<?php

namespace App\Services;

use App\Http\Clients\DBClient;

class CreateUserManager{
    private DBClient $DBClient;

    public function __construct(DBClient $DBClient){
        $this->DBClient=$DBClient;
    }

    /**
     * @throws \Exception
     */
    public function createUser(string $username, string $password): bool
    {
        if($this->DBClient->searchUser($username)){
            throw new \Exception("Conflict: The username already exists", 409);
        }

        try{
            $this->DBClient->createUser($username, $password);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }

        return true;
    }
}