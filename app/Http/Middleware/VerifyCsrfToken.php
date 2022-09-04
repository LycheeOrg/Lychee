<?php

namespace App\Http\Middleware;

use App\Exceptions\Internal\QueryBuilderException;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

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
	 * if an Authorization value is provided in the header and matches the
	 * token of a user.
	 *
	 * @param Request $request
	 * @param Closure $next
	 *
	 * @return mixed
	 *
	 * @throws TokenMismatchException
	 * @throws QueryBuilderException
	 */
	public function handle($request, Closure $next): mixed
	{
		$token = $request->header('Authorization');
		if ($request->is('api/*') && is_string($token)) {
			/** @var User|null $user */
			$user = User::query()
				->where('token', '=', hash('SHA512', $token))
				->first();
			if ($user instanceof User) {
				Auth::loginUsingId($user->id);

				return $next($request);
			}
		}

		return parent::handle($request, $next);
	}
}
