<?php

use App\Http\Controllers\SeguimientoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/seguimiento', [SeguimientoController::class, 'index'])->name('seguimiento');
