<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Services\Auth\SessionOrTokenGuard;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var array<int,string>
	 */
	protected $except = [
		// entry points...
		'/php/index.php',
		'/api/Session::init',
		'/api/v2/Zip',
	];

	/**
	 * Attempts to verify the CSRF token unless an API token is provided.
	 *
	 * Note, if the API token is given but invalid (i.e. refers to a
	 * non-existing user), then {@link \App\Services\Auth\SessionOrTokenGuard}
	 * bails out.
	 *
	 * @param Request  $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 *
	 * @throws TokenMismatchException
	 */
	public function handle($request, \Closure $next): mixed
	{
		$token = $request->headers->get(SessionOrTokenGuard::HTTP_TOKEN_HEADER);
		if (is_string($token) && $token !== '') {
			return $next($request);
		}

		return parent::handle($request, $next);
	}

	/**
	 * Determine if the HTTP request uses a ‘read’ verb.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return bool
	 */
	protected function isReading($request)
	{
		if (str_starts_with($request->route()->uri, 'api/v2')) {
			return false;
		}

		return parent::isReading($request);
	}
}
