<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminCheck
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (!Session::get('login') || Session::get('UserID') != 0) {
			return response('false');
		}
		return $next($request);
	}


}