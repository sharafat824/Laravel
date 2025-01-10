<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExchangeController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/exchanges', [ExchangeController::class, 'searchExchange']); // List API
Route::post('/exchanges', [ExchangeController::class, 'fetchAndStore']); // Create API
