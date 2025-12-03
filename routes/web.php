<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MovieController::class, 'index']);
Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/people/{person:slug}', [PersonController::class, 'show'])->name('people.show');
Route::get('/genres/{genre:slug}', [MovieController::class, 'byGenre'])->name('genres.show');
Route::get('/years/{year}', [MovieController::class, 'byYear'])->name('years.show');
