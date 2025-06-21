<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::post('/verify', [VerificationController::class, 'verify'])->name('verification.verify');
