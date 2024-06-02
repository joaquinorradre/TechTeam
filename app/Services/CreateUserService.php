<?php

namespace App\Services;

use App\Http\Clients\DBClient;
use Exception;

class CreateUserService
{
    protected $dbClient;

    public function __construct(DBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    /**
     * @throws Exception
     */
    public function createUser(string $username, string $password): true
    {
        if ($this->dbClient->userExistsInDatabase($username)) {
            throw new Exception('El nombre de usuario ya existe', 409);
        }

        $this->dbClient->createUser($username, $password);

        return true;
    }
}