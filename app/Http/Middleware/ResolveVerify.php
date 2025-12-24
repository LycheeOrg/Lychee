<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use LycheeVerify\Contract\VerifyInterface;
use LycheeVerify\Verify;

class ResolveVerify
{
	public function handle(Request $request, \Closure $next)
	{
		// Compute ONCE
		$verify = $this->resolveVerify($request);

		// Store for the lifetime of THIS request
		$request->attributes->set('verify', $verify);
		$request->attributes->set('status', $this->resolveStatus($request));

		return $next($request);
	}

	protected function resolveVerify(Request $request): VerifyInterface
	{
		$verify = new Verify();
		app()->instance(VerifyInterface::class, $verify);
		app()->instance(Verify::class, $verify);

		return app(VerifyInterface::class);
	}

	protected function resolveStatus(Request $request)
	{
		return $this->resolveVerify($request)->get_status();
	}
}
