<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTimelineRequest;
use App\Http\Requests\GetUsersRequest;
use App\Serializers\UserDataSerializer;
use App\Serializers\UserListSerializer;
use App\Services\GetUsersService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GetUsersController extends Controller
{
    private UserDataSerializer $userSerializer;
    private GetUsersService $getUsersService;

    public function __construct(GetUsersService $getUsersService, UserDataSerializer $userSerializer)
    {
        $this->getUsersService = $getUsersService;
        $this->userSerializer = $userSerializer;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(GetUsersRequest $request): JsonResponse
    {
        $userId = $request->input('id');

        try {
            $userData = $this->getUsersService->execute($userId);
            $serializedUserData = $this->userSerializer->serialize($userData);

            return new JsonResponse($serializedUserData, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Exception $exception) {
            if ($exception->getCode() === Response::HTTP_SERVICE_UNAVAILABLE) {
                return new JsonResponse([
                    'error' => 'Service Unavailable',
                    'message' => 'No se pueden devolver usuarios en este momento, inténtalo más tarde'
                ], Response::HTTP_SERVICE_UNAVAILABLE);
            }
            return new JsonResponse([
                'error' => 'Internal Server Error',
                'message' => 'Error del servidor al obtener los usuarios'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
