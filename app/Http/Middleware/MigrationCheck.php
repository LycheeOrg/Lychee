<?php

namespace App\Http\Middleware;

use App\Contracts\LycheeException;
use App\Exceptions\MigrationRequiredException;
use App\Http\Middleware\Checks\IsMigrated;
use Closure;
use Illuminate\Http\Request;

class MigrationCheck
{
	private IsMigrated $isMigrated;

	public function __construct(IsMigrated $isMigrated)
	{
		$this->isMigrated = $isMigrated;
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
	 */
	public function handle(Request $request, Closure $next): mixed
	{
		if (!$this->isMigrated->assert()) {
			throw new MigrationRequiredException();
		}

		return $next($request);
	}
}
