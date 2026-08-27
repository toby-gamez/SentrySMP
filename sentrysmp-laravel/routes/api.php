<?php

use App\Http\Controllers\Api\ActivePlayersController;
use App\Http\Controllers\Api\BanListController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\PlayerCountController;
use App\Http\Controllers\Api\StatusController;
use Illuminate\Support\Facades\Route;

Route::get('/status', [StatusController::class, 'status']);

Route::middleware('auth.api')->group(function () {
    Route::get('/commands/pending', [CommandController::class, 'pending']);
    Route::post('/commands/acknowledge', [CommandController::class, 'acknowledge']);
    Route::post('/player-count', [PlayerCountController::class, 'update']);
    Route::post('/active-players', [ActivePlayersController::class, 'update']);
    Route::post('/bans', [BanListController::class, 'update']);
});
