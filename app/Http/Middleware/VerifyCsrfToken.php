<?php

namespace App\Http\Middleware;

use App\Configs;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var array
	 */
	protected $except = [
		// entry point...
		'/php/index.php',
	];

	/**
	 * The goal of this function is to allow to bypass the CSRF token requirement
	 * if an Authorization value is provided in the header and matches the apiKey.
	 *
	 * FIXME: Do we want to hash this API key ? Might actually be a good idea...
	 *
	 * @param $request
	 * @param Closure $next
	 *
	 * @return mixed
	 *
	 * @throws TokenMismatchException
	 */
	public function handle($request, Closure $next)
	{
		if ($request->is('api/*')) {
			/**
			 * default value is ''
			 * we force it in case of the migration has not been done.
			 */
			$apiKey = Configs::get_value('api_key', '');

			/*
			 * if apiKey is the empty string we directly return the parent handle.
			 */
			if ($apiKey && $apiKey == '') {
				return parent::handle($request, $next);
			}

			/*
			 * We are currently checking for Authorization.
			 * Do we also want to check if there is a POST value with the apiKey ?
			 */
			if ($apiKey && $request->header('Authorization') === $apiKey) {
				return $next($request);
			}
		}

		return parent::handle($request, $next);
	}
}
