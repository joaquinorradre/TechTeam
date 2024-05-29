<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Services\CreateUserManager;
use Illuminate\Http\JsonResponse;

class CreateUserController extends Controller {

    private CreateUserManager $createUserManager;

    //serializer

    public function __construct(CreateUserManager $createUserManager)
    {
        $this->createUserManager=$createUserManager;
    }

    public function __invoke(CreateUserRequest $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');
        try {
            if ($this->createUserManager->createUser($username, $password)) {
                $response = [
                    'username' => $username,
                    'message' => 'Usuario creado correctamente',
                ];
            }
            return new JsonResponse($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e){
            $statusCode = $e->getCode();
            if ($statusCode === 409) {
                return response()->json([
                    'error' => 'Conflict',
                    'message' => 'El nombre de usuario ya existe',
                ], $statusCode);
            } else{
                return response()->json([
                    'error' => 'Internal Server Error',
                    'message' => 'Error del servidor al crear el usuario',
                ], $statusCode);
            }
        }
    }
}