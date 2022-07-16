<?php

namespace App\Http\Middleware;

use App\Facades\AccessControl;
use App\Models\User;
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
	 * The goal of this function is to allow to bypass the CSRF token requirement
	 * if an Authorization value is provided in the header and matches the apiKey.
	 *
	 * FIXME: Do we want to hash this API key ? Might actually be a good idea...
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
		if ($request->is('api/*')) {
			$token = $request->header('Authorization');
			if ($token === '') {
				return parent::handle($request, $next);
			}

			/** @var User|null $user */
			$user = User::query()
				->where('token', '=', $token)
				->where('token', '!=', '')
				->first();
			if ($user === null) {
				return parent::handle($request, $next);
			}

			if (!AccessControl::is_logged_in()) {
				AccessControl::log_as_id($user->id);
			}

			return $next($request);
		}

		return parent::handle($request, $next);
	}
}
