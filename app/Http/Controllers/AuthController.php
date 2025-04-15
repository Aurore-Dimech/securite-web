<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PhpParser\Error;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class AuthController extends Controller
{
    public function register(Request $request) {

        if(!$request->name || !$request->email || !$request->password) {
            return response()->json(['error' => 'Name, email and password can not be empty'], 400);
        }

        User::create([
            'name' => $request->name, 
            'email' => $request->email,
            'password'=> Hash::make($request->password),
            'role_id' => 1
        ]);
    }

    public function login(Request $request) {

        if(!$request->email || !$request->password) {
            return response()->json(['error' => 'Email and password can not be null'], 400);
        }

        $user = User::where("email", $request->email)->first();

        if(empty($user) || !Hash::check($request->password, $user->password)){
            return response()->json(['error' => 'Incorrect credentials, please try again'], 400);
        }
        
        $payload = JWTFactory::customClaims([
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => now()->timestamp,
            'nbf' => now()->timestamp,
            'jti' => uniqid(),
        ])->make();

        $token = JWTAuth::encode($payload);

        return response()->json([
            'access_token' => $token->get()
        ]);
    }

    public function show(Request $request)
    {
        $user_informations = [
            "name" => $request->user->name,
            "email" => $request->user->email,
        ];
        return $user_informations;
    }

    public function index()
    {
        return User::all(['name', 'email']);
    }

}
