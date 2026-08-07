<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use App\Repositories\ConfigManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts a route to the single configured instance owner (`config('owner_id')`),
 * mirroring the check already performed by {@see \App\Rules\OwnerIdRule} for
 * form-field-scoped owner checks.
 */
class OwnerOnly
{
	public function __construct(
		private ConfigManager $config_manager,
	) {
	}

	public function handle(Request $request, \Closure $next): mixed
	{
		if (Auth::id() === null || Auth::id() !== $this->config_manager->getValueAsInt('owner_id')) {
			throw new UnauthorizedException('Only the owner can do this.');
		}

		return $next($request);
	}
}
