<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //ログイン時と登録時には、認証を行わない
        if($this->path() == 'api/login' || $this->path() == 'api/users'){
            return true;
        }

        //AccessTokenが送信されていれば、それを検証
        if($this->hasHeader('AccessToken')){
            $input_token = $this->header('AccessToken'); 
            //ヘッダに書かれたアクセストークンがあるかどうかの検証
            $db_token = \DB::table('personal_access_tokens')
                        ->where('token', "$input_token")
                        ->value('token');
            if($db_token){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        //ログイン時は送られてくるusenameはデータベースに存在するはずなので、uniqueを外す。
        if($this->path() == 'api/login'){
            return [
                'username' => 'required|regex:/^[a-z0-9_]{1,15}$/i',
                'password' => 'required|regex:/^[a-z0-9_]{5,30}$/i'
            ];
        }elseif($this->path() == 'api/tweets' && $this->isMethod('post')){
            return [
                'content' => 'required|max:200'
            ];
        }else{
            //それ以外の場合(徳録・更新の場合)は、ユニークであるべき。
            return [
                'username' => 'required|unique:users,username|regex:/^[a-z0-9_]{1,15}$/i',
                'password' => 'required|regex:/^[a-z0-9_]{5,30}$/i'
            ];
        }
    }
}
