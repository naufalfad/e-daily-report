<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/tes-pohon-organisasi', function () {
    return view('organisasi');
});

Route::view('/login', 'auth.login')->name('login');
Route::get('/', fn() => redirect()->route('login'));
Route::view('/staf/dashboard', 'staf.dashboard')
    ->name('staf.dashboard');
Route::get('/staf/input-lkh', function () {
    return view('staf.input-lkh');
})->name('staf.input-lkh');
