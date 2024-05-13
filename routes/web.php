<?php

use App\Http\Controllers\GetStreamsController;
use App\Http\Controllers\GetTimelineController;
use App\Http\Controllers\GetUsersController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streams', GetStreamsController::class);
Route::get('/analytics/users', GetUsersController::class);
Route::get('/analytics/timeline', GetTimelineController::class);
