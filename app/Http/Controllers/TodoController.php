<?php

namespace App\Http\Controllers;

use App\Events\TodoUpdates;
use App\Models\Todo;
use App\Http\Resources\TodoResource;
use App\Services\TodoService;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Services\PusherBeamsService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TodoController extends Controller
{
    protected TodoService $todoService;
    protected PusherBeamsService $beams;

    public function __construct(TodoService $todoService, PusherBeamsService $beams)
    {
        $this->todoService = $todoService;
        $this->beams = $beams;
    }

    /**
     * @group Tâches (Todos)
     * @unauthenticated
     *
     * Récupère la liste de toutes les tâches.
     *
     * @response 200 [{"id": 1, "title": "Acheter du pain", "description": "À la boulangerie", "is_completed": false, "created_at": "2025-12-09T12:00:00.000000Z", "updated_at": "2025-12-09T12:00:00.000000Z"}]
     */
    public function index()
    {
        $todos = $this->todoService->getAllTodos();

        return TodoResource::collection($todos);
    }

    /**
     * @group Tâches (Todos)
     * @unauthenticated
     *
     * Crée une nouvelle tâche.
     *
     * @bodyParam title string required Le titre de la tâche. Exemple: Payer les factures
     * @bodyParam description string La description détaillée de la tâche. Exemple: Électricité et gaz
     * @bodyParam is_completed boolean Indique si la tâche est terminée (false par défaut). Exemple: false
     *
     * @response 201 {"id": 2, "title": "Payer les factures", "description": "Électricité et gaz", "is_completed": false, "created_at": "2025-12-09T12:05:00.000000Z", "updated_at": "2025-12-09T12:05:00.000000Z"}
     * @response 422 {"message": "The given data was invalid.","errors": {"title": ["The title field is required."]}}
     */
    public function store(StoreTodoRequest $request)
    {
        $todo = $this->todoService->createTodo($request->validated());

        event(new TodoUpdates($todo));
        $this->beams->publishToAll([
            'title' => 'Nouvelle tâche !',
            'body'  => $todo->title,
        ]);

        return new TodoResource($todo);
    }

    /**
     * @group Tâches (Todos)
     * @unauthenticated
     *
     * Affiche une tâche spécifique par son ID.
     *
     * @urlParam todo required L'ID de la tâche. Exemple: 1
     * @response 200 {"id": 1, "title": "Acheter du pain", "description": "À la boulangerie", "is_completed": false, "created_at": "2025-12-09T12:00:00.000000Z", "updated_at": "2025-12-09T12:00:00.000000Z"}
     * @response 404 {"message": "No query results for model [App\\Models\\Todo] 10."}
     */
    public function show(Todo $todo)
    {
        return new TodoResource($todo);
    }

    /**
     * @group Tâches (Todos)
     * @unauthenticated
     *
     * Met à jour une tâche existante.
     *
     * @urlParam todo required L'ID de la tâche à mettre à jour. Exemple: 1
     * @bodyParam title string Le nouveau titre de la tâche. Exemple: Faire le ménage
     * @bodyParam is_completed boolean Le statut de complétion. Exemple: true
     *
     * @response 200 {"id": 1, "title": "Faire le ménage", "description": "À la boulangerie", "is_completed": true, "created_at": "2025-12-09T12:00:00.000000Z", "updated_at": "2025-12-09T12:06:00.000000Z"}
     */
    public function update(UpdateTodoRequest $request, Todo $todo)
    {
        $this->todoService->updateTodo($todo, $request->validated());

        try {
            $response = $this->beams->publishToUser($todo->user_id, [
                'title' => 'Tâche modifié !',
                'body'  => "Une tâche a été modifiée : {$todo->title}",
            ]);

            Log::info("PusherBeams success", (array) $response);
        } catch (\Exception $e) {

            Log::error("PusherBeams ERROR", [
                "msg" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
        }

        return new TodoResource($todo->fresh());
    }


    /**
     * @group Tâches (Todos)
     * @unauthenticated
     *
     * Supprime une tâche spécifique.
     *
     * @urlParam todo required L'ID de la tâche à supprimer. Exemple: 1
     * @response 204
     */
    public function destroy(Todo $todo): Response
    {
        $this->todoService->deleteTodo($todo);

        return response()->noContent();
    }
}
