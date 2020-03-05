<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\Redirections\ToHome;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
		// base safety
		if (file_exists(base_path('installed.log'))) {
			return ToHome::go();
		}

		// This is the second safety:
		// Assume you do a "git pull" but forget to do the migration,
		// the installed.log will not be created!!!
		if (Schema::hasTable('configs')) {
			return ToHome::go();
		}

		return $next($request);
	}
}