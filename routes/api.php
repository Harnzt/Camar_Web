<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuyerDashboardController;
use App\Http\Controllers\Api\EmissionCalculationController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SellerDashboardController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::get('/airports', [CalculatorController::class, 'airports'])
    ->middleware('throttle:60,1');

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1');

    Route::get('/home', HomeController::class);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::post('/chatbot/send', [ChatbotController::class, 'send'])
        ->middleware('throttle:30,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::middleware('role:buyer')->group(function () {
            Route::get('/buyer/dashboard', BuyerDashboardController::class);
            Route::get('/buyer/transactions', [OrderController::class, 'buyerTransactions']);
            Route::get('/buyer/recommendations', [ProjectController::class, 'recommendations']);
            Route::post('/orders', [OrderController::class, 'store']);
            Route::post('/orders/charge', [OrderController::class, 'charge']);
            Route::post('/calculations', [CalculatorController::class, 'store']);
            Route::get('/calculations/latest', [EmissionCalculationController::class, 'latest']);
            Route::delete('/calculations', [CalculatorController::class, 'clear']);
        });

        Route::middleware('role:seller')->group(function () {
            Route::get('/seller/dashboard', SellerDashboardController::class);
            Route::get('/seller/projects', [SellerDashboardController::class, 'projects']);
            Route::post('/seller/projects', [SellerDashboardController::class, 'store']);
            Route::put('/seller/projects/{project}', [SellerDashboardController::class, 'update']);
            Route::delete('/seller/projects/{project}', [SellerDashboardController::class, 'destroy']);
            Route::get('/seller/transactions', [SellerDashboardController::class, 'transactions']);
        });
    });
});
