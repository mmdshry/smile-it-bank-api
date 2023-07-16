<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\TransferController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1/accounts')->group(static function () {
    Route::post('/', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/{account}/balance', [AccountController::class, 'getBalance'])->name('accounts.getBalance');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('/{account}/transfers', [TransferController::class, 'index'])->name('transfers.index');
});
