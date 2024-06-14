<?php

namespace App\Services;

use App\Http\Clients\ApiClient;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class StreamerExistManager
{
    private const string API_STREAMER_URL = 'https://api.twitch.tv/helix/users';
    private TwitchTokenService $twitchTokenService;
    private ApiClient $apiClient;
    public function __construct(TwitchTokenService $twitchTokenService,ApiClient $apiClient)
    {
        $this->twitchTokenService = $twitchTokenService;
        $this->apiClient = $apiClient;
    }

    /**
     * @throws Exception
     */
    public function getStreamer(string $streamerId): bool
    {
        try {
            $token = $this->twitchTokenService->getToken();
        } catch (Exception $exception) {
            if($exception->getCode() == Response::HTTP_UNAUTHORIZED) {
                throw new Exception(' Token de autenticación no proporcionado o inválido', Response::HTTP_UNAUTHORIZED);
            }
            throw $exception;
        }
        $userIdLink = self::API_STREAMER_URL . "?id=$streamerId";

        $apiResponse = $this->apiClient->makeCurlCall($userIdLink, $token);

        $status = $apiResponse['status'];
        $respuesta = $apiResponse['response'];

        if ($status >= 200 && $status < 300) {
            $responseData = json_decode($respuesta, true);
            return !empty($responseData['data']);
        }
        throw new Exception('El streamer ' . $streamerId .' especificado no existe en la API', Response::HTTP_NOT_FOUND);
    }
}