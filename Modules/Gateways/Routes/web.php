<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Modules\Gateways\Http\Controllers\AzampesaController;
use Modules\Gateways\Http\Controllers\SelcomController;
use Modules\Gateways\Http\Controllers\Web\Admin\SmsLogController;

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

// Admin SMS Logs Routes
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function () {
    Route::group(['prefix' => 'gateways', 'as' => 'gateways.'], function () {
        Route::group(['prefix' => 'sms-logs', 'as' => 'sms-logs.'], function () {
            Route::get('/', [SmsLogController::class, 'index'])->name('index');
            Route::get('/{id}', [SmsLogController::class, 'show'])->name('show');
            Route::delete('/{id}', [SmsLogController::class, 'destroy'])->name('destroy');
            Route::post('/clear', [SmsLogController::class, 'clearAll'])->name('clear');
        });
    });
});
