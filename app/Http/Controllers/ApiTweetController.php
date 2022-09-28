<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Tweet;
use App\Models\User;
use App\Http\Controllers\ApiUserController;
use App\Http\Requests\UserRequest;

class ApiTweetController extends Controller
{
    //認証もバリデーションも必要ないため、普通のRequesクラス
    public function getAllTweets(Request $request){
        $tweets = Tweet::get()->toJson(JSON_PRETTY_PRINT);
        return response($tweets, 200);
    }

    public function createTweet(UserRequest $request){
        //ツイートの処理
        $input = $request->validated();
        $input_content = $input["content"];

        //バリデーションの文字数制限がよくわからなかったので、とりあえずここで140文字か検証。
        if(mb_strlen($input_content) > 140){
            return response("ツイートは140文字以内にしてください。", 400);
        }

        //トークンに該当するユーザーのIDを取得
        $input_token = $request->header('AccessToken');
        $user_id = \DB::table('personal_access_tokens')
                    ->where('token', "$input_token")
                    ->value('tokenable_id');

        $tweet = new Tweet;
        $tweet->user_id = $user_id;
        $tweet->content = $input_content;
        $tweet->save();
        return response("Created", 201)
                ->header('Location', $_ENV['APP_URL'] . "/tweets/{$tweet->id}");
    }

    //リクエストにボディがないのでバリデーションに引っ掛かってしまうため、引数をRequestクラスにしました。
    public function deleteTweet(Request $request, $id){
        //トークンに該当するユーザーのIDを取得
        $input_token = $request->header('AccessToken');
        $user_id = \DB::table('personal_access_tokens')
                    ->where('token', "$input_token")
                    ->value('tokenable_id');

        $tweet = Tweet::where('id', $id)->first();

        if($tweet->user_id == $user_id){
            $tweet->delete();
            return response("該当のツイートは削除されました。", 200);
        }else{
            return response("他人のツイートです。", 401);
        }
    }

    //getAllTweets同様、認証$バリデーションが不要
    public function showTweet(Request $request, $id){
        $tweet = Tweet::where('id' , $id)->first();
        if(is_null($tweet)){
            return response("Tweetが見つかりません", 404);
        }
        $tweet = json_decode($tweet, true);
        return response($tweet, 200);
    }
}