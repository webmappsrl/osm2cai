<?php

use App\Models\HikingRoute;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CasLoginController;
use App\Http\Controllers\ImportUGCController;
use App\Http\Controllers\HikingRouteController;
use App\Http\Controllers\HikingRouteLoscarponeExportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/')->name('home');
Route::prefix('/emulatedUser')->name('emulatedUser.')->group(function () {
    Route::get('/restore', [\App\Http\Controllers\EmulateUserController::class, 'restore'])->name('restore');
});


/**
 * Route to login to application with cas with specific middleware and controller
 */
Route::get('/nova/cas-login', CasLoginController::class . '@casLogin')
    ->middleware('cai.cas');

/**
 * Route to logout from application and cas with facade and specific middleware
 */
Route::get('/nova/cas-logout', function () {
    cas()->logout();
})->middleware('cas.auth');


// Excel exports
Route::get('loscarpone/export/', [HikingRouteLoscarponeExportController::class, 'export'])->name('loscarpone-export');

Route::get('/hiking-route/id/{id}', function ($id) {
    $hikingroute = HikingRoute::find($id);
    if ($hikingroute == null) {
        abort(404);
    }
    return view('hikingroute', [
        'hikingroute' => $hikingroute
    ]);
})->name('hiking-route-public-page');

Route::get('hiking-route-map/{id}', function ($id) {
    return view('hikingroutemap', ['hikingroute' => HikingRoute::findOrFail($id)]);
})->name('hiking-route-public-map');

Route::get('/hiking-route/{id}/issues', [HikingRouteController::class, 'showIssuesChronology'])->name('hiking-route-public-issues')->name('hiking-route-issues');
Route::get('/import-ugc', [ImportUGCController::class, 'importUGCFromGeohub'])->name('import-ugc');
