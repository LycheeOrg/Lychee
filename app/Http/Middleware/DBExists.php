<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\Redirections\ToInstall;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DBExists
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
		if (!Schema::hasTable('configs')) {
			return ToInstall::go();
		}

		return $next($request);
	}
}