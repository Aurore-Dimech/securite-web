<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $requiredAuthorization
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $requiredAuthorization): Response
    {
        $user = $request->user;

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $role = $user->role; 
        if (!$role || !$role->$requiredAuthorization) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if($request->image && $requiredAuthorization === 'can_add_product' && !$role->can_add_image_to_product){
            return response()->json(['error' => 'Only premium users can add images to products'], 403);
        }

        return $next($request);
    }
}