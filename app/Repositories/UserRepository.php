<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function create(array $data)
    {
        return User::create($data);
    }

    public function paginate(int $perPage = 10)
    {
        return User::latest()->paginate($perPage);
    }
}
