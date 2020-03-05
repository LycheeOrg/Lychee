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
		// this should not happen but you never know.
		// if the key is not provided AND
		//	 the database (config table exists) is set
		//   or installed.log exists
		// this will generate an infinite loop. We do not want that.
		if (file_exists(base_path('.NO_SECURE_KEY')))
		{
			return $next($request);
		}

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