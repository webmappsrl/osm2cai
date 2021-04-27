<?php

use App\Http\Controllers\RegionController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\SectorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('api.')->group(function () {
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('geojson')->name('geojson.')->group(function () {
        Route::get('/region/{id}', [RegionController::class, 'geojson'])->name('region');
        Route::get('/province/{id}', [ProvinceController::class, 'geojson'])->name('province');
        Route::get('/area/{id}', [AreaController::class, 'geojson'])->name('area');
        Route::get('/sector/{id}', [SectorController::class, 'geojson'])->name('sector');
    });
    Route::prefix('shapefile')->name('shapefile.')->group(function () {
        Route::get('/region/{id}', [RegionController::class, 'shapefile'])->name('region');
        Route::get('/province/{id}', [ProvinceController::class, 'shapefile'])->name('province');
        Route::get('/area/{id}', [AreaController::class, 'shapefile'])->name('area');
        Route::get('/sector/{id}', [SectorController::class, 'shapefile'])->name('sector');
    });
});
