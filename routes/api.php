<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GameChallengeController;
use App\Http\Controllers\GameRoundController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // auth routes
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('register', 'store');
        Route::post('login', 'signin');
        Route::post('refresh-token', 'refresh');
        Route::get('check', 'checkAuthUser')->middleware(AuthMiddleware::class);
    });

    // authenticated routes
    Route::middleware([AuthMiddleware::class])->group(function () {
        Route::post('logout', [AuthController::class, 'signout']);

        // friends routes
        Route::prefix('friends')->group(function () {
            Route::get('/', [AuthController::class, 'listFriends']);
            Route::post('search', [AuthController::class, 'searchFriends']);
            Route::post('challenge', [GameChallengeController::class, 'store']);
            Route::post('remove', [AuthController::class, 'removeFriend']);
            Route::get('details/{token}', [AuthController::class, 'friendDetails']);
        });

        // game round routes
        Route::prefix('game')->group(function () {
            Route::get('/', [GameRoundController::class, 'index']);
            Route::post('round', [GameRoundController::class, 'store']);
            Route::get('/{gameRound}', [GameRoundController::class, 'show']);
            Route::put('/{gameRound}', [GameRoundController::class, 'update']);
        });

        // score routes
        Route::prefix('score')->group(function () {
            Route::post('/', [\App\Http\Controllers\GameScoreController::class, 'store']);
            Route::get('/{gameScore}', [\App\Http\Controllers\GameScoreController::class, 'show']);
        });

    });

});
