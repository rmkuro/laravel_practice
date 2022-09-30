<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->hasHeader('AccessToken')){
            $input_token = $this->header('AccessToken');
            $hashed_token = hash('sha256', $input_token);
            //ヘッダに書かれたアクセストークンがあるかどうかの検証
            $db_token = \DB::table('personal_access_tokens')
                        ->where('token', $hashed_token)
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
        return [
            'username' => 'required|unique:users,username|regex:/^[a-z0-9_]{1,15}$/i',
            'password' => 'required|regex:/^[a-z0-9_]{5,30}$/i'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => 400,
            'errors' => $validator->errors(),
        ],400);
        throw new HttpResponseException($response);
    }
}
