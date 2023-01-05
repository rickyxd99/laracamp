<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user();
     
        if (($role == 'admin' && !$user->is_admin)||($role == 'user' && $user->is_admin)) {
            abort(403);
        }
        return $next($request); 
    }
}