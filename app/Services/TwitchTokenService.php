<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use App\Http\Clients\DBClient;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class TwitchTokenService
{
    private DBClient $dbClient;
    private ApiClient $apiClient;

    public function __construct(DBClient $dbClient, ApiClient $apiClient)
    {
        $this->dbClient = $dbClient;
        $this->apiClient = $apiClient;
    }

    /**
     * @throws Exception
     */
    public function getToken(): string
    {
        $databaseTokenResponse = $this->dbClient->getTokenFromDatabase();

        if ($databaseTokenResponse != null) {
            return $databaseTokenResponse;
        }

        try {
            $apiTokenResponse = $this->apiClient->getTokenFromAPI();
            $result = json_decode($apiTokenResponse, true);

            if (isset($result['access_token'])) {
                $this->dbClient->addTokenToDatabase($result['access_token']);
                return $result['access_token'];
            }
            else {
                throw new Exception("No se pudo obtener el token de la API de Twitch");
            }
        } catch (Exception $exception) {
            throw new Exception('No se puede establecer conexión con Twitch en este momento', Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}