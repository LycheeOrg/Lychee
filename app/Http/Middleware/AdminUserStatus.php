<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\AdminUserAlreadySetException;
use App\Exceptions\AdminUserRequiredException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Http\Middleware\Checks\HasAdminUser;
use Illuminate\Http\Request;

/**
 * Class AdminUserStatus.
 *
 * This middleware ensures that the admin user has been set has the required status.
 * If the installation has the required status, then the request passes
 * unchanged.
 * If the required status equals `:set` but the admin user is
 * unset, then the client is redirected to the create admin user page.
 * If the required status equals `:unset` but the admin user is
 * set, then the client is redirected to the home page.
 * The latter mode is supposed to be used as a gatekeeper to the installation
 * pages and to prevent access if an admin user has been set.
 */
class AdminUserStatus
{
	public const SET = 'set';
	public const UNSET = 'unset';

	private HasAdminUser $hasAdminUser;

	public function __construct(HasAdminUser $hasAdminUser)
	{
		$this->hasAdminUser = $hasAdminUser;
	}

	/**
	 * Handles an incoming request.
	 *
	 * @param Request  $request        the incoming request to serve
	 * @param \Closure $next           the next operation to be applied to the
	 *                                 request
	 * @param string   $requiredStatus the required installation status; either
	 *                                 {@link self::SET} or
	 *                                 {@link self::UNSET}
	 *
	 * @return mixed
	 *
	 * @throws LycheeException
	 */
	public function handle(Request $request, \Closure $next, string $requiredStatus): mixed
	{
		if ($requiredStatus === self::SET) {
			if ($this->hasAdminUser->assert()) {
				return $next($request);
			} else {
				throw new AdminUserRequiredException();
			}
		} elseif ($requiredStatus === self::UNSET) {
			if ($this->hasAdminUser->assert()) {
				throw new AdminUserAlreadySetException();
			} else {
				return $next($request);
			}
		} else {
			throw new LycheeInvalidArgumentException('$requiredStatus must either be "' . self::SET . '" or "' . self::UNSET . '"');
		}
	}
}
