<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Tweet;
use App\Models\User;
use App\Http\Requests\UserRequest;

class ApiUserController extends Controller
{
    public function createUser(UserRequest $request){
        $input = $request->validated();

        $input_name = $input['username'];
        $user = User::where('username' , $input_name)->first();
        
        $new_user = new User;
        $new_user->username = $input_name;
        $new_user->password = password_hash($input['password'], PASSWORD_DEFAULT); 
        $new_user->save();

        return response("Created", 201)
                ->header('Location', "http://localhost/users/{$new_user->id}");
    }

    public function showUser(Request $request, $id){
        $user = User::where('id' , $id)->first();
        if(is_null($user)){
            return response("該当するユーザーが見つかりません", 404);
        }
        $user = json_decode($user, true);
        return response($user, 200);
    }

    public function updateUser(Request $request){
        //basicAuthentication関数は、認証に問題があればResopnseクラス、問題なければログイン対象のUserクラスのオブジェクトを返す
        $authentication_result = $this->basicAuthentication($request);

        //もしもレスポンスクラスのオブジェクトだったらエラーが発生している。
        if($authentication_result instanceof Response){
            return $authentication_result;
        }
        
        //分かりやすく、$userに格納
        $user = $authentication_result;

        //入力内容(ID,PW)に問題がないか検証
        $input = $request->validated();

        $user->username = $input["username"];
        $user->password = password_hash($input['password'], PASSWORD_DEFAULT);
        $user->save();

        return response(json_encode($user), 200);
    }
}