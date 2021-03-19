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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('geojson')->group(function () {
    Route::get('/region/{id}', [RegionController::class, 'geojson']);
    Route::get('/province/{id}', [ProvinceController::class, 'geojson']);
    Route::get('/area/{id}', [AreaController::class, 'geojson']);
    Route::get('/sector/{id}', [SectorController::class, 'geojson']);
});
