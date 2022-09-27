<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\Tweet;
use App\Models\User;

class ApiTweetController extends Controller
{
    public function getAllTweets(Request $request){
        $tweets = Tweet::get()->toJson(JSON_PRETTY_PRINT);
        return response($tweets, 200);
    }

    public function createTweet(Request $request){
        //認証した後、responseオブジェクトが返ってくる。成功した場合、Userのオブジェクトが、Responseオブジェクトに入っている。
        $authentication_result = $this->basicAuthentication($request);

        if($authentication_result instanceof Response){            
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

    public function deleteTweet(Request $request, $id){
        //認証した後、responseオブジェクトが返ってくる。成功した場合、Userのオブジェクトが、Responseオブジェクトに入っている。
        $authentication_result = $this->basicAuthentication($request);

        if($authentication_result instanceof Response){            
            //return $authentication_result;
            return $authentication_result;
        }
        
        //分かりやすく、$userという変数に移行
        $user = $authentication_result;

        $tweet = Tweet::where('id', $id)->first();
        if($tweet->user_id == $user->id){
            $tweet->delete();
            return response("該当のツイートは削除されました。", 200);
        }else{
            return response("他人のツイートです。", 401);
        }
    }

    public function showTweet(Request $request, $id){
        $tweet = Tweet::where('id' , $id)->first();
        if(is_null($tweet)){
            return response("Tweetが見つかりません", 404);
        }
        $tweet = json_decode($tweet, true);
        return response($tweet, 200);
    }
}
