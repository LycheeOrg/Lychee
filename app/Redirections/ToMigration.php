<?php

namespace App\Redirections;

use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ToMigration implements Redirection
{
	/**
	 * @throws RouteNotFoundException
	 * @throws FrameworkException
	 */
	public static function go(): RedirectResponse
	{
		try {
			return redirect(route('migrate'), Response::HTTP_TEMPORARY_REDIRECT, [
				'Cache-Control' => 'no-cache, must-revalidate',
			]);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
