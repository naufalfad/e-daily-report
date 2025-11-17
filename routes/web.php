<?php

use Illuminate\Support\Facades\Route;

// routes/web.php
Route::view('/login', 'auth.login')->name('login');
Route::get('/', fn() => redirect()->route('login'));
Route::view('/staf/dashboard', 'staf.dashboard')
    ->name('staf.dashboard');
Route::get('/staf/input-lkh', function () {
    return view('staf.input-lkh');
})->name('staf.input-lkh');