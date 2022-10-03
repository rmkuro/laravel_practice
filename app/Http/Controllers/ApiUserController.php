<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Tweet;
use App\Models\User;
use App\Models\Token;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\TweetRequest;

class ApiUserController extends Controller
{
    public function login(LoginRequest $request){
        $input_name = $request->username;
        $input_pass = $request->password;
        $user = User::where('username' , $input_name)->first();
        //$userがnull = 存在しないユーザーネームが入力されている。
        if(!$user){
            return response("ログイン失敗です", 401);
        }
        $hashed_password = $user->password;
        if(password_verify($input_pass, $hashed_password)){
            $token = $user->createToken('test');
            return response($token);
        }
        return response("ログイン失敗です", 401);
    }

    public function createUser(CreateUserRequest $request){
        $input_name = $request->username;
        $input_pass = $request->password;
        
        $new_user = new User;
        $new_user->username = $input_name;

        $new_user->password = password_hash($input_pass, PASSWORD_DEFAULT); 
        $new_user->save();
        return response("Created", 201)
                ->header('Location', $_ENV['APP_URL'] . "/tweets/{$new_user->id}");
    }

    public function showUser(Request $request, $id){
        $user = User::find($id)->first();
        if(is_null($user)){
            return response("該当するユーザーが見つかりません", 404);
        }
        $user = json_decode($user, true);
        return response($user, 200);
    }

    public function updateUser(UpdateUserRequest $request){
        $input = $request->validated();

        $input_token = $request->header('AccessToken');
        $hashed_token = hash('sha256', $input_token);
        //送られてきたトークンに該当するユーザーのIDを取得。(フォームリクエストで既に認証されてるのでNULLにはならない。)
        $user_id = Token::where('token', $hashed_token)
                    ->value('tokenable_id');

        $user = User::find($user_id);

        $user->username = $request->username;
        $user->password = password_hash($request->password, PASSWORD_DEFAULT);
        $user->save();

        return response(json_encode($user), 200);
    }
}