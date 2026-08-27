<?php

use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NoteController::class, 'index'])->name('notes.index');
Route::post('/biometrics/authenticate', [NoteController::class, 'authenticate'])->name('biometrics.authenticate');
Route::post('/biometrics/lock', [NoteController::class, 'lock'])->name('biometrics.lock');
Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
Route::delete('/notes/{id}', [NoteController::class, 'destroy'])->name('notes.destroy');
