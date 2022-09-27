<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\ApiTweetController;

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

Route::post('users', [ApiUserController::class, 'createUser']);
Route::get('users/{id}', [ApiUserController::class, 'showUser']);
Route::put('users/me', [ApiUserController::class, 'updateUser']);
Route::get('tweets', [ApiTweetController::class, 'getAllTweets']);
Route::post('tweets',[ApiTweetController::class, 'createTweet']);
Route::delete('tweets/{id}',[ApiTweetController::class, 'deleteTweet']);
Route::get('tweets/{id}',[ApiTweetController::class, 'showTweet']);

//開発用に1次的に使用
//Route::post('test/input', [ApiController::class, 'check_input']);
//Route::get('test/auth', [ApiController::class, 'basicAuthentication']);