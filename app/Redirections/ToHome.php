<?php

namespace App\Redirections;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ToHome implements Redirection
{
	/**
	 * @throws RouteNotFoundException
	 */
	public static function go()
	{
		// we directly redirect to gallery
		return redirect(route('home'), Response::HTTP_TEMPORARY_REDIRECT, [
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}
}

