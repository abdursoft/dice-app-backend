<?php
namespace App\Http\Middleware;

use App\Http\Controllers\Essentials\JWTAuth;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PusherMiddleware
{
 /**
  * Handle an incoming request.
  *
  * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
  */
 public function handle($request, Closure $next)
 {
  $token = $request->bearerToken();

  if (! $token) {
   return response()->json(['error' => 'Token not provided'], 401);
  }

  try {
   $decoded = JWTAuth::verifyToken($token, false);
   $user    = User::find($decoded->id);

   if (! $user || $user->role !== 'user') {
    return response()->json(['error' => 'Unauthorized'], 401);
   }

   // ✅ This makes $request->user() return the JWT user
   $request->setUserResolver(fn() => $user);

   return $next($request);
  } catch (\Exception $e) {
   return response()->json(['error' => 'Invalid token'], 401);
  }
 }

}
