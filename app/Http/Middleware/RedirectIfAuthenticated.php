<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
	    if (Auth::guard($guard)->check()) {
		    if (Auth::user()->role === 'student')
			    return redirect('/class/list');
		    if (Auth::user()->role === 'lecturer')
			    return redirect('/lecturer/home');
		    if (Auth::user()->role === 'admin')
			    return redirect('/admin/home');
	    }

        return $next($request);
    }
}
