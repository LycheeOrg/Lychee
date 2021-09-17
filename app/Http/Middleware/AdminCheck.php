<?php

namespace App\Http\Middleware;

use App\Facades\AccessControl;
use App\Http\Middleware\Checks\IsInstalled;
use Closure;
use Illuminate\Http\Request;

class AdminCheck
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
		if (!$this->isInstalled->assert()) {
			return $next($request);
		}

		if (!AccessControl::is_admin()) {
			return response('false');
		}

		return $next($request);
	}
}
