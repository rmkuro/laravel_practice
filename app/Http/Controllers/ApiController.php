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

    public function createTweet(Request $request){
        //認証した後、responseオブジェクトが返ってくる。成功した場合、Modelクラス(User)のオブジェクトが、Responseオブジェクトに入っている。
        $authentication_result = $this->basicAuthentication($request);

        //条件式を、!$authentication_result->status() == 200だと意図した通りに動かない理由が不明
        if(!($authentication_result->status() == 200)){            
            return $authentication_result;
        }
        
        //レスポンスオブジェクトのコンテンツの中にあるjson文字列を配列に変換
        $user = json_decode($authentication_result->content(), true);

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
        $tweet->user_id = $user['id'];
        $tweet->content = $tweet_content;
        $tweet->save();
        return response("Created", 201);
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

        return response($user, 200);
    }
}
