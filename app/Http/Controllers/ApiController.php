<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tweet;
use App\Models\User;

class ApiController extends Controller
{
    public function createUser(Request $request){
        $user = new User;
        $user->name = $request->name;
        $user->password = $request->password;
        $user->save();

        //return response()->json(
            //{
                #ユーザー登録の処理を書く。
            //};
        //)
    }

    public function getAllTweets(Request $request){
        $tweets = Tweet::get()->toJson(JSON_PRETTY_PRINT);
        return response($tweets, 200);
    }
}
