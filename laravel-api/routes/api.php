<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{UserController, AuthController, TransactionController};

Route::get('/health', function () {
    return response()->json(['up' => true]);
});

Route::post('/users', [UserController::class, 'store']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
});
