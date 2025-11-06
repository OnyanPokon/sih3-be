<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PersonilController;
use App\Http\Controllers\UnorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('personil')->group(function () {
        Route::get('/', [PersonilController::class, 'index']);
        Route::post('/', [PersonilController::class, 'store']);
        Route::get('/{id}', [PersonilController::class, 'show']);
        Route::put('/{id}', [PersonilController::class, 'update']);
        Route::put('/update-profil/{id}', [PersonilController::class, 'updateProfil']);
        Route::delete('/{id}', [PersonilController::class, 'destroy']);
    });

    Route::prefix('unor')->group(function () {
        Route::get('/', [UnorController::class, 'index']);
        Route::post('/', [UnorController::class, 'store']);
        Route::get('/{id}', [UnorController::class, 'show']);
        Route::put('/{id}', [UnorController::class, 'update']);
        Route::put('/update-profil/{id}', [UnorController::class, 'updateProfil']);
        Route::delete('/{id}', [UnorController::class, 'destroy']);
    });

    Route::prefix('kategori')->group(function () {
        Route::get('/', [KategoriController::class, 'index']);
        Route::post('/', [KategoriController::class, 'store']);
        Route::get('/{id}', [KategoriController::class, 'show']);
        Route::put('/{id}', [KategoriController::class, 'update']);
        Route::delete('/{id}', [KategoriController::class, 'destroy']);
    });
});
