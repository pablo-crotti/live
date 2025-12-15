<?php

namespace App\Services;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Collection;

class TodoService
{

    public function getAllTodos(): Collection
    {
        return Todo::all();
    }

    public function createTodo(array $data): Todo
    {
        return Todo::create($data);
    }

    public function getTodoById(int $id): ?Todo
    {
        return Todo::find($id);
    }

    public function updateTodo(Todo $todo, array $data): bool
    {
        return $todo->update($data);
    }

    public function deleteTodo(Todo $todo): bool
    {
        return $todo->delete();
    }

    public function deleteAllTodos()
    {
        return Todo::query()->delete();
    }
}
