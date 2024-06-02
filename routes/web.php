<?php

use App\Http\Controllers\GetStreamsController;
use App\Http\Controllers\GetUsersController;
use App\Http\Controllers\PostStreamerController;
use App\Http\Controllers\DeleteStreamerController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ValidateCsrfToken;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([ValidateCsrfToken::class])->group(function () {
    Route::get('/analytics/formDelete', function () {
        return view('form-delete');
    });
    Route::get('/analytics/formPost', function () {
        return view('form-post');
    });
    Route::get('/analytics/streams', GetStreamsController::class);
    Route::get('/analytics/streamers', GetUsersController::class);
    Route::post('/analytics/follow', PostStreamerController::class);
    Route::delete('/analytics/unfollow', DeleteStreamerController::class);
});
