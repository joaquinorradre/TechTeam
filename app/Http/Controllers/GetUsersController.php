<?php

namespace App\Http\Controllers;

use App\Serializers\UserDataSerializer;
use App\Services\GetStreamsService;
use App\Services\GetUsersService;
use App\Services\UserDataManager;
use App\Http\Requests\GetUsersRequest;
use Illuminate\Http\JsonResponse;

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

        $userData = $this->getUsersService->execute($userId);
        $serializedUserData = $this->userSerializer->serialize($userData);

        return response()->json($serializedUserData, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
