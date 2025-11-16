<?php

use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\DokumenArtikelController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KuesionerController;
use App\Http\Controllers\PemetaanController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PersonilController;
use App\Http\Controllers\PosPengamatanController;
use App\Http\Controllers\ProdukHukumController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ThumbnailArtikelController;
use App\Http\Controllers\UnorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('landing')->group(function () {
    Route::prefix('artikel')->group(function () {
        Route::get('/', [ArtikelController::class, 'landing']);
        Route::get('/{slug}', [ArtikelController::class, 'detail']);
        Route::get('/download/{id}', [DokumenArtikelController::class, 'download']);
    });
    Route::prefix('pemetaan')->group(function () {
        Route::get('/', [PemetaanController::class, 'landing']);
        Route::get('/by-kategori', [PemetaanController::class, 'byKategori']);
    });
    Route::prefix('produk-hukum')->group(function () {
        Route::get('/produk-hukum', [ProdukHukumController::class, 'landing']);
        Route::get('/produk-hukum/{slug}', [ProdukHukumController::class, 'detail']);
    });
    Route::prefix('berita')->group(function () {
        Route::get('/berita', [BeritaController::class, 'landing']);
        Route::get('/berita/{slug}', [BeritaController::class, 'detail']);
    });
    Route::prefix('kuesioner')->group(function () {
        Route::post('/', [KuesionerController::class, 'store']);
    });
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('role')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
    });

    Route::prefix('permission')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::post('/', [PermissionController::class, 'store']);
        Route::get('/{id}', [PermissionController::class, 'show']);
        Route::put('/{id}', [PermissionController::class, 'update']);
        Route::delete('/{id}', [PermissionController::class, 'destroy']);
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

    Route::prefix('berita')->group(function () {
        Route::get('/', [BeritaController::class, 'index']);
        Route::post('/', [BeritaController::class, 'store']);
        Route::get('/{id}', [BeritaController::class, 'show']);
        Route::put('/{id}', [BeritaController::class, 'update']);
        Route::delete('/{id}', [BeritaController::class, 'destroy']);
    });

    Route::prefix('produk-hukum')->group(function () {
        Route::get('/', [ProdukHukumController::class, 'index']);
        Route::post('/', [ProdukHukumController::class, 'store']);
        Route::get('/{id}', [ProdukHukumController::class, 'show']);
        Route::put('/{id}', [ProdukHukumController::class, 'update']);
        Route::delete('/{id}', [ProdukHukumController::class, 'destroy']);
    });

    Route::prefix('thumbnail-artikel')->group(function () {
        Route::get('/', [ThumbnailArtikelController::class, 'index']);
        Route::post('/', [ThumbnailArtikelController::class, 'store']);
        Route::get('/{id}', [ThumbnailArtikelController::class, 'show']);
        Route::put('/{id}', [ThumbnailArtikelController::class, 'update']);
        Route::delete('/{id}', [ThumbnailArtikelController::class, 'destroy']);
    });

    Route::prefix('dokumen-artikel')->group(function () {
        Route::get('/', [DokumenArtikelController::class, 'index']);
        Route::post('/', [DokumenArtikelController::class, 'store']);
        Route::get('/{id}', [DokumenArtikelController::class, 'show']);
        Route::put('/{id}', [DokumenArtikelController::class, 'update']);
        Route::delete('/{id}', [DokumenArtikelController::class, 'destroy']);
    });

    Route::prefix('pos-pengamatan')->group(function () {
        Route::get('/', [PosPengamatanController::class, 'index']);
        Route::post('/', [PosPengamatanController::class, 'store']);
        Route::get('/{id}', [PosPengamatanController::class, 'show']);
        Route::put('/{id}', [PosPengamatanController::class, 'update']);
        Route::delete('/{id}', [PosPengamatanController::class, 'destroy']);
    });

    Route::prefix('kuesioner')->group(function () {
        Route::get('/', [KuesionerController::class, 'index']);
        Route::get('/export', [KuesionerController::class, 'export']);
        Route::get('/{id}', [KuesionerController::class, 'show']);
        Route::delete('/{id}', [KuesionerController::class, 'destroy']);
    });
});
