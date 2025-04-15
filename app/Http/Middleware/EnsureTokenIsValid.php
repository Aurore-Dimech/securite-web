<?php

namespace App\Http\Middleware;

use Error;
use Closure;
use App\Models\User;
use Tymon\JWTAuth\Token;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{

            $token = $request->bearerToken();
    
            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }

            if(now()->timestamp - JWTAuth::decode(new Token($token))['iat'] > 3600){
                return response()->json(['error' => 'Expired token'], 401);
            }
    
            $payload = JWTAuth::setToken($token)->getPayload();
    
            $userId = $payload->get('sub');
    
            $user = User::find($userId);
    
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $request->merge(['user' => $user]);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }


        return $next($request);
    }
}
