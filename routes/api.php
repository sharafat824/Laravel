<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\ExchangePriceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/exchanges', [ExchangeController::class, 'searchExchange']); // List API
Route::get('/exchanges/data', [ExchangeController::class, 'fetchAndStore']); // Create API


Route::post('exchange-prices', [ExchangePriceController::class, 'show']); // List API

