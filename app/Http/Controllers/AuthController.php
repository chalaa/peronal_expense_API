<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Contracts\Providers\JWT;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTAuth as JWTAuthJWTAuth;

class AuthController extends Controller
{
    // register the User
    public function register(Request $request){
        $request->validate([
            'name' => ['required','string'],
            'email'=> ['required','string','email'],
            'password'=> ['required','string','confirmed','min:8']
        ]);

        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> Hash::make($request->password),
        ]);

        // Authenticate user
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status'=> 'success',
            'user'=> $user,
            'authorisation' =>  [
                'token'=> $token,
                'type' => 'bearer'
            ]
        ],200);

    }

    // login
    public function login(Request $request){
        // validate the request
        $request->validate([
            "email"=> ["required","email"],
            "password"=> ["required","string",'min:8'],
        ]);
        
        $credential = $request->only('email','password');

        if (!Auth::attempt($credential)){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credential'
            ],401);
        }

        // Authenticate user
        $user = Auth::user();
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'status'=> 'success',
            'user'=> $user,
            'authorisation' =>  [
                'token'=> $token,
                'type' => 'bearer'
            ]
        ],200);
    }

    // logout

    public function logout(){
        Auth::logout();
        return response()->json([
            'status'=> 'success',
            'message'=> 'Successfully logged out'
        ]);
    }

    /*
    
    Refresh method
    This method invalidates the user Auth 
    token and generates a new token

    */

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
