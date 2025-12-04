<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/reset-password/{token}', function ($token) {
    return response()->json(['token' => $token]);
})->name('password.reset');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Admin Only Routes
    Route::middleware('is_admin')->group(function () {
        // User management routes can go here
    });

    // Librarian & Admin Routes
    Route::middleware('is_librarian')->group(function () {
        // Book Management
        Route::post('/books', [BookController::class, 'store']);
        Route::put('/books/{id}', [BookController::class, 'update']);
        Route::delete('/books/{id}', [BookController::class, 'destroy']);
        
        // Loan Management (Overdue & Return)
        Route::get('/loans/overdue', [LoanController::class, 'overdue']);
        Route::post('/loans/{id}/return', [LoanController::class, 'returnBook']);
    });

    // Authenticated User Routes
    Route::post('/loans/borrow', [LoanController::class, 'borrow']);
    Route::get('/loans/my-history', [LoanController::class, 'myHistory']);
});

// Public Book Routes
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);
