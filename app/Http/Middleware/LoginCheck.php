<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginCheck
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (!Session::get('login')) {
			return response('false');
		}
		return $next($request);
	}
}
