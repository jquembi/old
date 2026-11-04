<?php

use App\Http\Controllers\InstaladorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InstaladorController::class, 'index'])->name('index');
Route::get('token', [InstaladorController::class, 'token'])->name('token');

Route::middleware('throttle:60,1')->group(function () {
    Route::post('testar-base', [InstaladorController::class, 'testarBase'])->name('testar-base');
    Route::post('passo/{passo}', [InstaladorController::class, 'passo'])->whereNumber('passo')->name('passo');
});
