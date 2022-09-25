<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tweet;
use App\Models\User;

class ApiController extends Controller
{
    public function createUser(Request $request){
        //$user = new User;
        $input = json_decode($request->getContent(), true);

        //$inputにusername,passwordのキーが存在し、かつ$inputがfalseでない(Json形式の入力である)ことを保証している
        
        if(!($input && array_key_exists('username', $input) && array_key_exists('password', $input))){
            return response('{"username" : "xx" , "password" : "xx"}の形式にしてください。', 400);
        }

        $input_name = $input['username'];
        $user = User::where('name' , $input_name)->first();
        
        //$userがNULLでない=入力されたユーザー名と同じデータが存在する
        if(isset($user)){
            $conflict_id = $user->value('id');
            return response("ユーザー名は既に使われています", 409)
                    ->header('Location', "http://localhost/users/{$conflict_id}");
        }

        //ユーザーネームが15文字以上の場合、不適
        if(!preg_match('/^[a-z0-9_]{1,15}$/i', $input_name)){
            return response("ユーザーネームは英数字、_(アンダーバー)のみの15文字以内にしてください。", 400);
        }

        //パスワードを5文字以上、30文字以内に収まっていない場合、不適
        if(!preg_match('/^[a-z0-9_]{5,30}$/i', $input['password'])){
            return response("パスワードは英数字、_(アンダーバー)のみ、5文字以上、30文字以内にしてください。", 400);
        }
        
        $new_user = new User;
        $new_user->name = $input_name;
        $new_user->password = $input['password'];
        $new_user->save();

        return response("Created", 201)
                ->header('Location', "http://localhost/users/{$new_user->id}");
    }

    public function showUser(Request $request, $id){
        $user = User::where('id' , $id)->first();
        $user = json_decode($user, true);
        return response($user, 200);
    }

    public function updateUser(Request $request){
        //basicAuthentication関数は、認証に問題があればResopnseクラス、問題なければログイン対象のUserクラスのオブジェクトを返す
        $authentication_result = $this->basicAuthentication($request);

        //もしもレスポンスクラスのオブジェクトだったらエラーが発生している。
        if(is_a($authentication_result, 'Response')){            
            return $authentication_result;
        }
        
        //分かりやすく、$userに格納
        $user = $authentication_result;

        //入力情報がJSON形式、かつusername,passwordというキーを持つことを保証している
        $input = json_decode($request->getContent(), true);
        if(!($input && array_key_exists('username', $input) && array_key_exists('password', $input))){
            return response('{"username" : "xx" , "password" : "xx"}の形式にしてください。', 400);
        }

        $input_name = $input['username'];

        //$userは既に使っているので、ここでは$search_userとする。
        $search_user = User::where('name' , $input_name)->first();
        
        //$userがNULLでない=入力されたユーザー名と同じデータが存在する
        if(isset($search_user)){
            $conflict_id = $user->value('id');
            return response("ユーザー名は既に使われています", 409)
                    ->header('Location', "http://localhost/users/{$conflict_id}");
        }

        //ユーザーネームが15文字以上の場合、不適
        if(!preg_match('/^[a-z0-9_]{1,15}$/i', $input_name)){
            return response("ユーザーネームは英数字、_(アンダーバー)のみの15文字以内にしてください。", 400);
        }

        //パスワードを5文字以上、30文字以内に収まっていない場合、不適
        if(!preg_match('/^[a-z0-9_]{5,30}$/i', $input['password'])){
            return response("パスワードは英数字、_(アンダーバー)のみ、5文字以上、30文字以内にしてください。", 400);
        }

        $user->name = $input["username"];
        $user->password = $input["password"];
        $user->save();

        return response(json_encode($user), 200);
    }

    public function getAllTweets(Request $request){
        $tweets = Tweet::get()->toJson(JSON_PRETTY_PRINT);
        return response($tweets, 200);
    }

    public function createTweet(Request $request){
        //認証した後、responseオブジェクトが返ってくる。成功した場合、Userのオブジェクトが、Responseオブジェクトに入っている。
        $authentication_result = $this->basicAuthentication($request);

        if(is_a($authentication_result, 'Response')){            
            return $authentication_result;
        }
        
        //分かりやすく、$userという変数に移行
        $user = $authentication_result;

        //ツイートの処理
        $input_content = json_decode($request->getContent(), true);
        if(!$input_content){
            return response("{\"content\": \"ツイート内容\"}の形式にしてください。", 400);
        }
        $tweet_content = $input_content["content"];

        if(mb_strlen($tweet_content) > 140){
            return response("ツイートは140文字以内にしてください。", 400);
        }

        $tweet = new Tweet;
        $tweet->user_id = $user->id;
        $tweet->content = $tweet_content;
        $tweet->save();
        return response("Created", 201)
                ->header('Location', "http://localhost/tweets/{$tweet->id}");
    }

    public function basicAuthentication(Request $request){
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
        $user_name = $user->value('name');
        $user_pass = $user->value('password');
        
        //ここも!の後ろの()がないと挙動がおかしい(ここは条件式が複雑だから、いずれにせよ付けた方がいいとは思うけど)
        if(!('Basic ' . base64_encode($user_name . ':' . $user_pass) == $auth_header)){
            return response('パスワードが違います', 401);
        }

        //認証に何も問題がなければ、Userクラスのオブジェクトを返す
        return $user;
    }
}

