<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('users', 'ApiController@getAllUsers');
Route::post('users/login', 'ApiController@LoginUser');
Route::get('users/{id}', 'ApiController@showUser');
Route::post('users/{id}', 'ApiController@updateUser');
Route::get('tweets','ApiController@getAllTweets');
Route::post('tweets','ApiController@createTweet');
Route::delete('tweets/{id}','ApiController@deleteTweet');
Route::get('tweets/{id}','ApiController@showTweet');