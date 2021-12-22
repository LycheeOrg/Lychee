<?php

namespace App\Http\Middleware;

use App\Contracts\LycheeException;
use App\Http\Middleware\Checks\ExistsDB;
use App\Redirections\ToInstall;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class DBExists
{
	private ExistsDB $existsDB;

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
	 *
	 * @throws LycheeException
	 * @throws RouteNotFoundException
	 */
	public function handle(Request $request, Closure $next): mixed
	{
		if (!$this->existsDB->assert()) {
			return ToInstall::go();
		}

		return $next($request);
	}
}
