<?php

namespace App\Http\Middleware;

use App\Facades\AccessControl;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Collection;
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
			$token = $request->header('Authorization');
			if (!$token) {
				return parent::handle($request, $next);
			}

			/** @var User $user */
			$user = User::query()->where('token', '=', $token)->get();
			if ($user instanceof Collection) {
				$user = $user->get(0);
			}

			if ($user === null) {
				return parent::handle($request, $next);
			}

			AccessControl::log_as_id($user->id);

			return $next($request);
		}

		return parent::handle($request, $next);
	}
}
