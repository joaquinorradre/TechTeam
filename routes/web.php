<?php

use App\Http\Controllers\GetStreamsController;
use App\Http\Controllers\GetUsersController;
use App\Http\Controllers\GetTopOfTheTops;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GetUsersFollowController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streams', GetStreamsController::class);
//Route::get('/analytics/users', GetUsersController::class);
Route::get('/analytics/topsofthetops', [GetTopOfTheTops::class, 'fetchData']);
Route::get('/analytics/users', GetUsersFollowController::class);