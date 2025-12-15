<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function register(RegisterRequest $request)
    {
        $this->auth->register($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully registered',
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->auth->login($request->email, $request->password);

        if (!$result) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'status'   => 'success',
            'message'  => 'Login successful',
            'user'     => new UserResource($result['user']),
            'token'    => $result['token'],
            'token_type' => 'Bearer',
        ]);
    }

    public function userInfo()
    {
        return UserResource::collection(
            $this->auth->listUsers()
        );
    }

    public function logout(Request $request)
    {
        $this->auth->logout($request->user());

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully logged out',
        ]);
    }
}
