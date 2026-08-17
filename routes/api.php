<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;


Route::get('/movies', function () {
    return response()->json(['status' => 'stub']);
});

Route::get('/movies', [MovieController::class, 'index']);
