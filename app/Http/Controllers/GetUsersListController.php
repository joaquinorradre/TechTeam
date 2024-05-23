<?php

namespace App\Http\Controllers;

use App\Services\UserListDataManager;
use App\Http\Clients\DBClient;
use Illuminate\Http\JsonResponse;

class GetUsersListController extends Controller
{
    private DBClient $dbClient;

    public function __construct(DBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    /**s
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        try {
            // Obtener los datos de usuarios y streamers utilizando el DBClient
            $userData = $this->dbClient->getUsersWithFollowedStreamers();

            // Retornar la respuesta exitosa con los datos de los usuarios y los streamers seguidos
            return new JsonResponse($userData, 200);
        } catch (Exception $exception) {
            // Retornar una respuesta de error si ocurre alguna excepción
            return new JsonResponse(['error' => $exception->getMessage()], 500);
        }
    }
}
