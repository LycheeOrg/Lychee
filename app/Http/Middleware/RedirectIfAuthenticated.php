<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
	/**
	 * NOT USED.
	 *
	 * Handle an incoming request.
	 *
	 * @param Request     $request
	 * @param Closure     $next
	 * @param string|null $guard
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = null)
	{
		if (Auth::guard($guard)->check()) {
			return redirect('/home');
		}

		return $next($request);
	}
}
