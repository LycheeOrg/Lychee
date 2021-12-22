<?php

namespace App\Http\Middleware;

use App\Contracts\InternalLycheeException;
use App\Http\Middleware\Checks\IsInstalled;
use App\Redirections\ToHome;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class InstalledCheck
{
	private IsInstalled $isInstalled;

	public function __construct(IsInstalled $isInstalled)
	{
		$this->isInstalled = $isInstalled;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 *
	 * @return mixed
	 *
	 * @throws InternalLycheeException
	 * @throws RouteNotFoundException
	 */
	public function handle(Request $request, Closure $next): mixed
	{
		if ($this->isInstalled->assert()) {
			return ToHome::go();
		}

		return $next($request);
	}
}
