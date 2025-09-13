<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GameRoundController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function(){

    // auth routes
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('register', 'store');
        Route::post('login', 'signin');
        Route::post('refresh-token', 'refresh');
        Route::get('check', 'checkAuthUser')->middleware(AuthMiddleware::class);
    });

    // game round routes
    Route::prefix('game-rounds')->group(function (){
        Route::get('/', [GameRoundController::class, 'index']);
        Route::post('/', [GameRoundController::class, 'store']);
        Route::get('/{gameRound}', [GameRoundController::class, 'show']);
        Route::put('/{gameRound}', [GameRoundController::class, 'update']);
    });
});
