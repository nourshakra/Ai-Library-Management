<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use Illuminate\Support\Facades\Route;

// ---------- Public / guest ----------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ---------- Authenticated (any role) ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Profile management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Browsing (both roles can read the catalog)
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // Personalized recommendations
    Route::get('/recommendations', [RecommendationController::class, 'index']);

    // Role-aware AI chatbot — RBAC is enforced INSIDE ChatbotService,
    // not by route middleware, since both roles may use the chatbot
    // but each gets a different, restricted data context.
    Route::post('/chatbot/ask', [ChatbotController::class, 'ask']);
    Route::get('/chatbot/history', [ChatbotController::class, 'history']);

    // ---------- Admin only ----------
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('users', AdminUserController::class);
        Route::apiResource('books', AdminBookController::class);
        Route::apiResource('categories', AdminCategoryController::class);
    });
});
