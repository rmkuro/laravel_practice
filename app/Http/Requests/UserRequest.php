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
        if($this->path() == 'api/users'){
            return true;
        }

        $auth_header = $this->headers->get('Authorization'); //Base64エンコードされたヘッダ情報を取得
        $access_token = base64_decode(substr($auth_header, 6), true); //ヘッダからID:PWの形にbase64デコード
        
        if(!$access_token){
            return false;
        }

        $input_name = substr($access_token, 0, strpos($access_token, ':')); //ユーザーネームを取得

        $user = User::where('name' , $input_name)->first(); //入力されたユーザー情報に該当するデータをUserモデルを介して取り出す
        if (is_null($user)){
            //$userがNULLの時点で、ユーザーネームが間違っている。
            return false;
        }
        
        //データベースからユーザーネーム・パスワードを取得
        $user_name = $user->name;
        $user_pass = $user->password;
        
        //ここも!の後ろの()がないと挙動がおかしい(ここは条件式が複雑だから、いずれにせよ付けた方がいいとは思うけど)
        if(!('Basic ' . base64_encode($user_name . ':' . $user_pass) == $auth_header)){
            return false;
        }

        //認証に何も問題がなければ、true
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'username' => 'required|unique:users,name|regex:/^[a-z0-9_]{1,15}$/i',
            'password' => 'required|regex:/^[a-z0-9_]{5,30}$/i'
        ];
    }
}
