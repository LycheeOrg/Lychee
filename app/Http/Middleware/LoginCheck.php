<?php

namespace App\Http\Middleware;

use App\Facades\AccessControl;
use Closure;
use Illuminate\Http\Request;

class LoginCheck
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 *
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (!AccessControl::is_logged_in()) {
			return response('false');
		}

		return $next($request);
	}
}
