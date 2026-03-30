<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\SharesController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Notes
    Route::get('/notes', [NotesController::class, 'index']);
    Route::post('/notes', [NotesController::class, 'store']);
    Route::post('/notes/sync', [NotesController::class, 'sync']);
    Route::delete('/notes/{url}', [NotesController::class, 'destroy']);

    // Shares
    Route::get('/shares', [SharesController::class, 'index']);
    Route::get('/shares/received', [SharesController::class, 'received']);
    Route::get('/shares/notes', [SharesController::class, 'sharedNotes']);
    Route::post('/shares/domain', [SharesController::class, 'shareByDomain']);
    Route::post('/shares/tag', [SharesController::class, 'shareByTag']);
    Route::delete('/shares/{id}', [SharesController::class, 'destroy']);
    Route::get('/users/search', [SharesController::class, 'searchUsers']);
});
