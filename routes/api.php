<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferController;

Route::post('/transfers', [TransferController::class, 'store']);

Route::post('/transfer', [TransferController::class, 'storeCompat']);
