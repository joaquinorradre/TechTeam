<?php

use App\Http\Controllers\GetStreamsController;
use App\Http\Controllers\GetTimelineController;
use App\Http\Controllers\GetTopOfTheTopsController;
use App\Http\Controllers\GetUsersController;
use App\Http\Controllers\GetUsersListController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streams', GetStreamsController::class);
//Route::get('/analytics/users', GetUsersController::class);
Route::get('/analytics/topsofthetops', GetTopOfTheTopsController::class);
