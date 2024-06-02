<?php

use App\Http\Controllers\GetStreamsController;
use App\Http\Controllers\GetTimelineController;
use App\Http\Controllers\GetUsersController;
use App\Http\Controllers\PostStreamerController;
use App\Http\Controllers\DeleteStreamerController;
use App\Http\Controllers\CreateUserController;
use App\Http\Controllers\GetTopOfTheTopsController;
use App\Http\Controllers\GetUsersFollowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streams', GetStreamsController::class);
Route::get('/analytics/timeline', GetTimelineController::class);
Route::get('/analytics/streamers', GetUsersController::class);
Route::get('/analytics/users', GetUsersFollowController::class);
Route::get('/analytics/topsofthetops', GetTopOfTheTopsController::class);
Route::post('/analytics/users', CreateUserController::class);
Route::post('/analytics/follow', PostStreamerController::class);
Route::delete('/analytics/unfollow', DeleteStreamerController::class);

