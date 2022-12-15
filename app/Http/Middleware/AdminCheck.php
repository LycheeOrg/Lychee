<?php

namespace App\Http\Middleware;

use App\Contracts\Exceptions\InternalLycheeException;
use App\Exceptions\UnauthorizedException;
use App\Http\Middleware\Checks\IsInstalled;
use App\Policies\UserPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminCheck
{
	private IsInstalled $isInstalled;

	public function __construct(IsInstalled $isInstalled)
	{
		$this->isInstalled = $isInstalled;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 *
	 * @throws UnauthorizedException
	 * @throws InternalLycheeException
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		if (!$this->isInstalled->assert()) {
			return $next($request);
		}

		if (!Gate::check(UserPolicy::IS_ADMIN)) {
			throw new UnauthorizedException('Admin privileges required');
		}

		return $next($request);
	}
}
