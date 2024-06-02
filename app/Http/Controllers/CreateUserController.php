<?php

namespace App\Http\Controllers;

class CreateUserController
{

use App\Http\Requests\CreateUserRequest;
use App\Services\CreateUserService;
use Illuminate\Http\JsonResponse;
use Exception;

class CreateUserController extends Controller
{
    private CreateUserService $createUserService;

    public function __construct(CreateUserService $createUserService)
    {
        $this->createUserService = $createUserService;
    }

    public function __invoke(CreateUserRequest $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (empty($username) || empty($password)) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'Parámetros inválidos'
            ], 400);
        }

        try {
            $this->createUserService->createUser($username, $password);
            return response()->json([
                'username' => $username,
                'message' => 'Usuario creado correctamente'
            ], 201);
        } catch (Exception $exception) {
            $status = $exception->getCode();
            if ($status === 409) {
                return response()->json([
                    'error' => 'Conflict',
                    'message' => 'El nombre de usuario ya existe'
                ], $status);
            }

            if ($status === 500) {
                return response()->json([
                    'error' => 'Internal Server Error',
                    'message' => 'Error del servidor al crear el usuario'
                ], $status);
            }

            return response()->json([
                'error' => 'Bad Request',
                'message' => 'Parámetros inválidos'
            ], 400);
        }
    }
}

