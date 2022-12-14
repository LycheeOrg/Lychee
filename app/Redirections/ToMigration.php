<?php

namespace App\Redirections;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ToMigration implements Redirection
{
	/**
	 * @throws RouteNotFoundException
	 * @throws BindingResolutionException
	 */
	public static function go(): RedirectResponse
	{
		return redirect(route('migrate'), Response::HTTP_TEMPORARY_REDIRECT, [
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}
}
