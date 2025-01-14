<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\MigrationAlreadyCompletedException;
use App\Exceptions\MigrationRequiredException;
use App\Http\Middleware\Checks\IsMigrated;
use Illuminate\Http\Request;

/**
 * Class MigrationStatus.
 *
 * This middleware ensures that the migration has the required status.
 * If the migration has the required status, then the request passes
 * unchanged.
 * If the required status equals `:complete` but the migration is
 * incomplete, then the client is redirected to the migration page.
 * If the required status equals `:incomplete` but the migration is
 * complete, then the client is redirected to the home page.
 * The latter mode is supposed to be used as a gatekeeper to the migration
 * pages and to prevent access if no migration is required.
 */
class MigrationStatus
{
	public const COMPLETE = 'complete';
	public const INCOMPLETE = 'incomplete';

	private IsMigrated $isMigrated;

	public function __construct(IsMigrated $isMigrated)
	{
		$this->isMigrated = $isMigrated;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request        the incoming request to serve
	 * @param \Closure $next           the next operation to be applied to the
	 *                                 request
	 * @param string   $requiredStatus the required migration status; either
	 *                                 {@link self::COMPLETE} or
	 *                                 {@link self::INCOMPLETE}
	 *
	 * @return mixed
	 *
	 * @throws LycheeException
	 */
	public function handle(Request $request, \Closure $next, string $requiredStatus): mixed
	{
		if ($requiredStatus === self::COMPLETE) {
			if ($this->isMigrated->assert()) {
				return $next($request);
			} else {
				throw new MigrationRequiredException();
			}
		} elseif ($requiredStatus === self::INCOMPLETE) {
			if ($this->isMigrated->assert()) {
				throw new MigrationAlreadyCompletedException();
			} else {
				return $next($request);
			}
		} else {
			throw new LycheeInvalidArgumentException('$requiredStatus must either be "' . self::COMPLETE . '" or "' . self::INCOMPLETE . '"');
		}
	}
}
