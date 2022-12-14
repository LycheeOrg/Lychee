<?php

namespace App\Redirections;

use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ToHome implements Redirection
{
	/**
	 * @throws RouteNotFoundException
	 */
	public static function go(): RedirectResponse
	{
		return redirect(route('home'), Response::HTTP_TEMPORARY_REDIRECT, [
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}
}
