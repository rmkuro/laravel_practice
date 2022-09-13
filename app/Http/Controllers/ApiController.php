<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getAllNotes(Request $request){
        $user = new User;
        $user->name = $request->name;
        $user->password = $request->password;
        $user->save();

        return response()->json(
            {
                session_start();
                “code” => 201,
                “Access Token” => “access token”,
                “Location” => “http://localhost:8000/users/{id}”,
                “home” => “http://localhost:8000/tweets”
            }
        )
    }
}
