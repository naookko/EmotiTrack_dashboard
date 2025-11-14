<?php

use App\Http\Controllers\Mongo\ResponseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/responses/month/{month}', [ResponseController::class, 'responsesByMonth']);
Route::get('/responses/summary', [ResponseController::class, 'monthlySummary']);
