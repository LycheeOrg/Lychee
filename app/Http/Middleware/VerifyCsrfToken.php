<?php

namespace App\Http\Middleware;

use App\Services\Auth\SessionOrTokenGuard;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var string[]
	 */
	protected $except = [
		// entry points...
		'/php/index.php',
		'/api/Session::init',
	];

	/**
	 * Attempts to verify the CSRF token unless an API token is provided.
	 *
	 * Note, if the API token is given but invalid (i.e. refers to a
	 * non-existing user), then {@link \App\Services\Auth\SessionOrTokenGuard}
	 * bails out.
	 *
	 * @param Request $request
	 * @param Closure $next
	 *
	 * @return mixed
	 *
	 * @throws TokenMismatchException
	 */
	public function handle($request, Closure $next): mixed
	{
		$token = $request->headers->get(SessionOrTokenGuard::HTTP_TOKEN_HEADER);
		if (is_string($token) && $token !== '') {
			return $next($request);
		}

		return parent::handle($request, $next);
	}
}
