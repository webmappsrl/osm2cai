<?php

use App\Models\HikingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\AssociateToRegions;
use App\Http\Controllers\CasLoginController;
use App\Http\Controllers\ExportCsvController;
use App\Http\Controllers\ImportUGCController;
use App\Http\Controllers\HikingRouteController;
use App\Http\Controllers\MiturAbruzzoMapsController;
use App\Http\Controllers\MigrationNoticeController;
use App\Console\Commands\AssociateMountainGroupsToRegions;
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

// Migration notice routes
Route::get('/migration-notice', [MigrationNoticeController::class, 'show'])->name('migration.notice');
Route::get('/migration-continue', [MigrationNoticeController::class, 'continue'])->name('migration.continue');

Route::get('/logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->middleware('auth')->name('logs');

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
Route::get('/loading-download/{type}/{model}/{id}', function () {
    return view('loading', [
        'type' => request()->type,
        'model' => request()->model,
        'id' => request()->id
    ]);
})->name('loading-download');

Route::get('/sync-to-regions', function () {
    try {
        $command = new AssociateToRegions(true);
        $result = $command->handle();
        $result == 0 ? $message = 'Sync completed, go back and refresh the page to see the results.' : $message = 'Sync failed';
        return response()->json([
            'message' => $message
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
})->name('sync-to-regions');

Route::get('/poi/map/{id}', [MiturAbruzzoMapsController::class, 'poiMap'])->name('poi-map');
Route::get('/mountain-groups/map/{id}', [MiturAbruzzoMapsController::class, 'mountainGroupsMap'])->name('mountain-groups-map');
Route::get('/cai-huts/map/{id}', [MiturAbruzzoMapsController::class, 'caiHutsMap'])->name('cai-huts-map');
Route::get('/mountain-groups-hr/map/{id}', [MiturAbruzzoMapsController::class, 'mountainGroupsHrMap'])->name('mountain-groups-hr-map');
Route::get('/export-form', [ExportCsvController::class, 'showForm'])->name('export.form');
Route::post('/export-csv', [ExportCsvController::class, 'export'])->name('export.csv');
