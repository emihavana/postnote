<?php

use App\Http\Controllers\MpesaCallbackController;
use Illuminate\Support\Facades\Route;

// This is your single public gateway for Safaricom!
Route::post('/mpesa/callback', [MpesaCallbackController::class, 'handleCallback']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
