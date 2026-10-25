<?php

use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy']);
