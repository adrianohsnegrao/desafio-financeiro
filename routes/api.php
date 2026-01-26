<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;

Route::post('/transfers', [TransferController::class, 'store']);

Route::post('/transfer', [TransferController::class, 'storeCompat']);

Route::get('/users', [UserController::class, 'index']);
