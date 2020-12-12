<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\Redirections\ToInstall;
use Closure;
use Illuminate\Database\QueryException;
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
		try {
			if (!Schema::hasTable('configs')) {
				return ToInstall::go();
			}
		} catch (QueryException $e) {
			return ToInstall::go();
		}

		return $next($request);
	}
}
