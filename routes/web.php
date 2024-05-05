<?php

use App\Http\Controllers\GetStreams;
use App\Http\Controllers\GetUsers;
use App\Http\Controllers\GetTopOfTheTops;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streams', GetStreams::class);
Route::get('/analytics/users', GetUsers::class);
Route::get('/analytics/topsofthetops', [GetTopOfTheTops::class, 'fetchData']);

