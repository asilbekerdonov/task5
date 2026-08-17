<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/movies', function () {
    return response()->json(['status' => 'stub']);
});
