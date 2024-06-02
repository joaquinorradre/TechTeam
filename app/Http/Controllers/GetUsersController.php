<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTimelineRequest;
use App\Serializers\UserListSerializer;
use App\Services\GetUsersService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GetUsersController extends Controller
{
    private UserListSerializer $userSerializer;
    private GetUsersService $getUsersService;


    public function __construct(GetUsersService $getUsersService, UserListSerializer $userSerializer)
    {
        $this->getUsersService = $getUsersService;
        $this->userSerializer = $userSerializer;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(GetTimelineRequest $request): JsonResponse
    {
        $userId = $request->input('id');

        $userData = $this->getUsersService->execute($userId);
        $serializedUserData = $this->userSerializer->serialize($userData);

        return new JsonResponse($serializedUserData, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
