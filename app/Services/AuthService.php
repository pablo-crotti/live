<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->users->create($data);
    }

    public function login(string $email, string $password)
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            return null;
        }

        $user = Auth::user();
        $token = $user->createToken('authToken')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function listUsers()
    {
        return $this->users->paginate();
    }

    public function logout($user)
    {
        $user->tokens()->delete();
    }
}
