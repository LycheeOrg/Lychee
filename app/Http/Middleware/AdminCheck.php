<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\MiddlewareFunctions\IsInstalled;
use App\ModelFunctions\SessionFunctions;
use Closure;
use Illuminate\Http\Request;

class AdminCheck
{
	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var IsInstalled
	 */
	private $isInstalled;

	public function __construct(SessionFunctions $sessionFunctions, IsInstalled $isInstalled)
	{
		$this->sessionFunctions = $sessionFunctions;
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
		if (!$this->isInstalled->assert()) {
			return $next($request);
		}

		if (!$this->sessionFunctions->is_admin()) {
			return response('false');
		}

		return $next($request);
	}
}
