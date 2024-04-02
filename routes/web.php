<?php

use App\Http\Controllers\getStreams;
use App\Http\Controllers\getUsers;
use App\Http\Controllers\getTopOftheTops;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics/streams', [GetStreams::class, 'getLiveStreams']);
Route::get('/analytics/users', [GetUsers::class, 'getUserInfo']);
Route::get('/analytics/topsofthetops', [GetTopOfTheTops::class, 'fetchData']);

