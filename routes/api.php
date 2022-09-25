<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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

Route::post('users', [ApiController::class, 'createUser']);
Route::get('users/{id}', [ApiController::class, 'showUser']);
Route::put('users/me', [ApiController::class, 'updateUser']);
Route::get('tweets', [ApiController::class, 'getAllTweets']);
Route::post('tweets',[ApiController::class, 'createTweet']);
Route::delete('tweets/{id}',[ApiController::class, 'deleteTweet']);
Route::get('tweets/{id}',[ApiController::class, 'showTweet']);

//開発用に1次的に使用
//Route::post('test/input', [ApiController::class, 'check_input']);
//Route::get('test/auth', [ApiController::class, 'basicAuthentication']);