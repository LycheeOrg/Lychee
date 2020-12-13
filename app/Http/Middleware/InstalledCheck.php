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
	 * @var IsInstalled
	 */
	private $isInstalled;

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
	 */
	public function handle($request, Closure $next)
	{
		if ($this->isInstalled->assert()) {
			return ToHome::go();
		}

		return $next($request);
	}
}
