<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'log.request'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::get('/projects',        [ProjectController::class, 'index']);
    Route::get('/projects/{id}',   [ProjectController::class, 'show']);
    Route::post('/projects',       [ProjectController::class, 'store'])->middleware('role:admin');
    Route::put('/projects/{id}',   [ProjectController::class, 'update'])->middleware('role:admin');
    Route::delete('/projects/{id}',[ProjectController::class, 'destroy'])->middleware('role:admin');

    Route::get('/projects/{project_id}/tasks', [TaskController::class, 'indexByProject']);
    Route::get('/tasks/{id}',                   [TaskController::class, 'show']);
    Route::post('/projects/{project_id}/tasks', [TaskController::class, 'store'])->middleware('role:manager');
    Route::delete('/tasks/{id}',                [TaskController::class, 'destroy'])->middleware('role:manager');
    Route::put('/tasks/{id}',[TaskController::class, 'update']);

    Route::get('/tasks/{task_id}/comments',  [CommentController::class, 'index']);
    Route::post('/tasks/{task_id}/comments', [CommentController::class, 'store']);
});
