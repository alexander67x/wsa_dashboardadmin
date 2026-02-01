<?php

use App\Http\Controllers\ArchivoDownloadController;
use App\Http\Controllers\ArchivoViewController;
use App\Http\Controllers\SeguimientoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/seguimiento', [SeguimientoController::class, 'index'])->name('seguimiento');
Route::get('/seguimiento/gantt', [SeguimientoController::class, 'gantt'])->name('seguimiento.gantt');
Route::get('/archivos/{archivo}/descargar', ArchivoDownloadController::class)
    ->middleware('signed')
    ->name('archivos.download');
Route::get('/archivos/{archivo}/ver', ArchivoViewController::class)
    ->middleware('signed')
    ->name('archivos.view');
