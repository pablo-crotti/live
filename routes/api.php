<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Pusher\PushNotifications\PushNotifications;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('users', [AuthController::class, 'userInfo']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('todos', TodoController::class);

    Route::middleware('auth:sanctum')->get('/beams-auth', function (Request $request) {
        $requestedBeamsId = $request->query('user_id');
        $authenticatedUserId = Auth::id();
        $expectedBeamsId = "user-{$authenticatedUserId}";

        if (!$authenticatedUserId || $requestedBeamsId !== $expectedBeamsId) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $beamsClient = new PushNotifications([
            'instanceId' => config('broadcasting.instance_id'),
            'secretKey'  => config('broadcasting.secret_key'),
        ]);

        return response()->json($beamsClient->generateToken($requestedBeamsId));
    });
});
