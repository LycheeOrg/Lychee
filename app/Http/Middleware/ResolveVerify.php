<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use LycheeVerify\Contract\VerifyInterface;
use LycheeVerify\Verify;

class ResolveVerify
{
	public function handle(Request $request, \Closure $next)
	{
		try {
			if (!Schema::hasTable('configs')) {
				return $next($request);
			}
			// @codeCoverageIgnoreStart
		} catch (\Throwable) {
			return $next($request);
		}

		// Compute ONCE
		$verify = $this->resolveVerify($request);

		// Store for the lifetime of THIS request
		$request->attributes->set('verify', $verify);

		return $next($request);
	}

	protected function resolveVerify(Request $request): VerifyInterface
	{
		$verify = resolve(Verify::class);
		app()->scoped(VerifyInterface::class, fn () => $verify);
		app()->scoped(Verify::class, fn () => $verify);

		return app(VerifyInterface::class);
	}
}
