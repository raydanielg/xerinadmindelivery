<?php

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

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Modules\Gateways\Http\Controllers\AzampesaController;
use Modules\Gateways\Http\Controllers\SelcomController;

Route::group(['prefix' => 'payment'], function () {

    //AZAMPESA
    Route::group(['prefix' => 'azampesa', 'as' => 'azampesa.'], function () {
        Route::get('pay', [AzampesaController::class, 'index'])->name('pay');
        Route::any('callback', [AzampesaController::class, 'callback'])->name('callback')
            ->withoutMiddleware([VerifyCsrfToken::class]);
    });

    //SELCOM
    Route::group(['prefix' => 'selcom', 'as' => 'selcom.'], function () {
        Route::get('pay', [SelcomController::class, 'index'])->name('pay');
        Route::any('callback', [SelcomController::class, 'callback'])->name('callback')
            ->withoutMiddleware([VerifyCsrfToken::class]);
        Route::any('cancel', [SelcomController::class, 'cancel'])->name('cancel')
            ->withoutMiddleware([VerifyCsrfToken::class]);
        Route::any('webhook', [SelcomController::class, 'webhook'])->name('webhook')
            ->withoutMiddleware([VerifyCsrfToken::class]);
    });
});
