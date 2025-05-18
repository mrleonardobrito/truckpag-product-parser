<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StatusController;

Route::get('/', [StatusController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{code}', [ProductController::class, 'show']);
Route::put('/products/{code}', [ProductController::class, 'update']);
Route::delete('/products/{code}', [ProductController::class, 'delete']);
