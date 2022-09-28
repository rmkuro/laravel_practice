<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Tweet;
use App\Models\User;

class ApiUserController extends Controller
{
    public function createUser(Request $request){
        $input = $request->validate([
            'username' => 'required|unique:users,name|regex:/^[a-z0-9_]{1,15}$/i',
            'password' => 'required|regex:/^[a-z0-9_]{5,30}$/i'
        ]);

        $input_name = $input['username'];
        $user = User::where('name' , $input_name)->first();
        
        $new_user = new User;
        $new_user->name = $input_name;
        $new_user->password = $input['password'];
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
        $input = $request->validate([
            'username' => 'required|unique:users,name|regex:/^[a-z0-9_]{1,15}$/i',
            'password' => 'required|regex:/^[a-z0-9_]{5,30}$/i'
        ]);

        $user->name = $input["username"];
        $user->password = $input["password"];
        $user->save();

        return response(json_encode($user), 200);
    }

    public static function basicAuthentication(Request $request){
        $auth_header = $request->headers->get('Authorization'); //Base64エンコードされたヘッダ情報を取得
        $access_token = base64_decode(substr($auth_header, 6), true); //ヘッダからID:PWの形にbase64デコード
        
        if(!$access_token){
            return response('base64エンコードされた文字列を送信してください。', 400);
        }

        $input_name = substr($access_token, 0, strpos($access_token, ':')); //ユーザーネームを取得

        $user = User::where('name' , $input_name)->first(); //入力されたユーザー情報に該当するデータをUserモデルを介して取り出す
        if (is_null($user)){
            //$userがNULLの時点で、ユーザーネームが間違っている。
            return response('正しいユーザーネームを入力してください', 400);
        }
        
        //データベースからユーザーネーム・パスワードを取得
        //->name　と ->value('name')の違いがわからない。
        $user_name = $user->name;
        $user_pass = $user->password;
        
        //ここも!の後ろの()がないと挙動がおかしい(ここは条件式が複雑だから、いずれにせよ付けた方がいいとは思うけど)
        if(!('Basic ' . base64_encode($user_name . ':' . $user_pass) == $auth_header)){
            return response('パスワードが違います', 401);
        }

        //認証に何も問題がなければ、Userクラスのオブジェクトを返す
        return $user;
    }
}