<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ===== Public (no auth needed) =====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ===== Protected (auth:sanctum) =====
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Books (read only for users)
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show']);

    // Categories (read only)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    // User profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Recommendations (user only)
    Route::get('/recommendations', [RecommendationController::class, 'index']);

    // Chatbot (for both admin and user)
    Route::post('/chatbot/ask', [ChatbotController::class, 'ask']);

});

// ===== Admin Only =====
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    // ===== Users Management =====
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::get('/admin/users/{user}', [UserController::class, 'show']);
    Route::put('/admin/users/{user}', [UserController::class, 'update']);
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy']);

    // ===== Categories Management =====
    Route::get('/admin/categories', [AdminCategoryController::class, 'index']);
    Route::post('/admin/categories', [AdminCategoryController::class, 'store']);
    Route::get('/admin/categories/{category}', [AdminCategoryController::class, 'show']);
    Route::put('/admin/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/admin/categories/{category}', [AdminCategoryController::class, 'destroy']);

    // ===== Books Management =====
    Route::get('/admin/books', [AdminBookController::class, 'index']);
    Route::post('/admin/books', [AdminBookController::class, 'store']);
    Route::get('/admin/books/{book}', [AdminBookController::class, 'show']);
    Route::put('/admin/books/{book}', [AdminBookController::class, 'update']);
    Route::delete('/admin/books/{book}', [AdminBookController::class, 'destroy']);

});
