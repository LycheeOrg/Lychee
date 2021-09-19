<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Authenticate extends Middleware
{
	/**
	 * Get the path the user should be redirected to when they are not authenticated.
	 *
	 * @param Request $request
	 *
	 * @return string|null
	 *
	 * @throws RouteNotFoundException
	 */
	protected function redirectTo($request): ?string
	{
		if ($request->expectsJson()) {
			return null;
		} else {
			return route('home');
		}
	}
}
