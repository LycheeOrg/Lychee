<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\MiddlewareFunctions\IsInstalled;
use App\Redirections\ToHome;
use Closure;
use Illuminate\Http\Request;

class InstalledCheck
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
		if (IsInstalled::assert()) {
			return ToHome::go();
		}

		return $next($request);
	}
}
