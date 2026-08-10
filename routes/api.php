<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Rotas públicas (não exigem token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas (exigem token válido do Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Usuários / Perfis
    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/users/suggestions', [UserController::class, 'suggestions']);
    Route::get('/users/{username}', [UserController::class, 'show']);
    Route::get('/users/{username}/posts', [PostController::class, 'byUsername']);
    Route::put('/profile', [UserController::class, 'update']);
    Route::post('/users/{username}/follow', [UserController::class, 'toggleFollow']);

    // Posts / Feed
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Curtidas
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);

    // Comentários
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});
