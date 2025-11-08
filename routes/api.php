<?php

use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PemetaanController;
use App\Http\Controllers\PersonilController;
use App\Http\Controllers\ThumbnailArtikelController;
use App\Http\Controllers\UnorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('landing')->group(function () {
    Route::get('/artikel', [ArtikelController::class, 'landing']);
    Route::get('/pemetaan', [PemetaanController::class, 'landing']);
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

    Route::prefix('pemetaan')->group(function () {
        Route::get('/', [PemetaanController::class, 'index']);
        Route::get('/{id}/geojson', [PemetaanController::class, 'showGeoJson'])
            ->name('pemetaan.geojson');
        Route::post('/', [PemetaanController::class, 'store']);
        Route::get('/{id}', [PemetaanController::class, 'show']);
        Route::put('/{id}', [PemetaanController::class, 'update']);
        Route::delete('/{id}', [PemetaanController::class, 'destroy']);
    });


    Route::prefix('artikel')->group(function () {
        Route::get('/', [ArtikelController::class, 'index']);
        Route::post('/', [ArtikelController::class, 'store']);
        Route::get('/{id}', [ArtikelController::class, 'show']);
        Route::put('/{id}', [ArtikelController::class, 'update']);
        Route::delete('/{id}', [ArtikelController::class, 'destroy']);
    });

    Route::prefix('thumbnail-artikel')->group(function () {
        Route::get('/', [ThumbnailArtikelController::class, 'index']);
        Route::post('/', [ThumbnailArtikelController::class, 'store']);
        Route::get('/{id}', [ThumbnailArtikelController::class, 'show']);
        Route::put('/{id}', [ThumbnailArtikelController::class, 'update']);
        Route::delete('/{id}', [ThumbnailArtikelController::class, 'destroy']);
    });
});
