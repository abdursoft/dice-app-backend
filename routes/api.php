<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GameChallengeController;
use App\Http\Controllers\GameRoundController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\PusherMiddleware;
use App\Models\GameRound;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => [PusherMiddleware::class]]);

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

        Route::post('change-name',[AuthController::class, 'profileData']);

        // friends routes
        Route::prefix('friends')->group(function () {
            Route::get('/', [AuthController::class, 'listFriends']);
            Route::post('search', [AuthController::class, 'searchFriends']);
            Route::post('challenge', [GameChallengeController::class, 'store']);
            Route::post('challenge-accept/{id}', [GameChallengeController::class, 'acceptChallenge']);
            Route::get('challenges', [GameChallengeController::class, 'index']);
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
            Route::delete('delete/{roundId}', [\App\Http\Controllers\GameScoreController::class, 'deleteScoreByRoundId']);
        });

    });

    // game statistics
    Route::get('game-stats',[AuthController::class, 'statistics']);

});

