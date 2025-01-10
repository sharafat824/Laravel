<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExchangeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/exchanges', [ExchangeController::class, 'searchExchange']); // List API
Route::post('/exchanges', [ExchangeController::class, 'fetchAndStore']); // Create API
