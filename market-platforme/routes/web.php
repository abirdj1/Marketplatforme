<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/reset-password/{token}', function () {
    return 'reset';
})->name('password.reset');
