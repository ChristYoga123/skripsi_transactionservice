<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('course')->group(function()
{   
    Route::get('dashboard/orders', [TransactionController::class, 'index']);
    Route::post('checkout/{slug}/daftar', [TransactionController::class, 'daftar']);
});
