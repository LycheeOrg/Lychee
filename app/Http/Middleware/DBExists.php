<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\Http\Middleware\Checks\ExistsDB;
use App\Redirections\ToInstall;
use Closure;
use Illuminate\Http\Request;

class DBExists
{
	/**
	 * @var
	 */
	private $existsDB;

	public function __construct(ExistsDB $existsDB)
	{
		$this->existsDB = $existsDB;
	}

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
		if (!$this->existsDB->assert()) {
			return ToInstall::go();
		}

		return $next($request);
	}
}
