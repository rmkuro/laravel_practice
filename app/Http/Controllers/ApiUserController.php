<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Tweet;
use App\Models\User;
use App\Http\Requests\UserRequest;

class ApiUserController extends Controller
{
    public function login(UserRequest $request){
        $input = $request->validated();
        $input_name = $input['username'];
        $input_pass = $input['password'];
        $input_pass = md5($input_pass); //入力されたパスワードのハッシュ化
        $user = User::where('username' , $input_name)->first();
        //$userがnull = 存在しないユーザーネームが入力されている。
        if(!$user){
            return response("ログイン失敗です", 401);
        }
        if($user->password == $input_pass){
            $token = $user->createToken('test');
            return response($token);
        }
        return response("ログイン失敗です", 401);
    }

    public function createUser(UserRequest $request){
        $input = $request->validated();

        $input_name = $input['username'];
        $input_pass = $input['password'];
        
        $new_user = new User;
        $new_user->username = $input_name;

        $new_user->password = md5($input_pass); 
        $new_user->save();
        return response("Created", 201)
                ->header('Location', $_ENV['APP_URL'] . "/tweets/{$new_user->id}");
    }

    public function showUser(Request $request, $id){
        $user = User::where('id' , $id)->first();
        if(is_null($user)){
            return response("該当するユーザーが見つかりません", 404);
        }
        $user = json_decode($user, true);
        return response($user, 200);
    }

    public function updateUser(UserRequest $request){
        $input = $request->validated();

        $input_token = $request->header('AccessToken');
        //送られてきたトークンに該当するユーザーのIDを取得。(フォームリクエストで既に認証されてるのでNULLにはならない。)
        $db_token = \DB::table('personal_access_tokens')
                    ->where('token', "$input_token")
                    ->value('tokenable_id');

        $user = User::where('id', $db_token)->first();

        $user->username = $input["username"];
        $user->password = md5($input['password']);
        $user->save();

        return response(json_encode($user), 200);
    }
}