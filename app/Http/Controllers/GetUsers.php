<?php

namespace App\Http\Controllers;

use App\Services\GetUsersService;
use Illuminate\Http\Request;

class GetUsers extends Controller
{
    private GetUsersService $getUserService;

    public function __construct(GetUsersService $getUserService)
    {
        $this->getUserService = $getUserService;
    }

    /**
     * Handle the incoming request
     */
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        if(!$request->has('id')){
            return response()->json(['error' => 'Se requiere el parámetro "id" en la URL'], 400);
        }
        $userId = $request->input('id');
        $userInfo = $this->getUserService->getUserInfo($userId);

        return response()->json($userInfo, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
