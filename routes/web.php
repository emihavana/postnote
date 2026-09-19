<?php

use Illuminate\Support\Facades\Route;
// Route::redirect('/', '/login')
//     ->middleware(['guest']);


Route::redirect('/', '/login')
    ->middleware(['guest']);



Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';