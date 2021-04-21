<?php

use Illuminate\Support\Facades\Route;

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
