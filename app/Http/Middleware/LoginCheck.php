<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthenticatedException;
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
	 *
	 * @throws UnauthenticatedException
	 */
	public function handle(Request $request, Closure $next)
	{
		if (!AccessControl::is_logged_in()) {
			throw new UnauthenticatedException();
		}

		return $next($request);
	}
}
