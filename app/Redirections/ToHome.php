<?php

namespace App\Redirections;

use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ToHome implements Redirection
{
	/**
	 * @throws RouteNotFoundException
	 */
	public static function go()
	{
		// we directly redirect to gallery
		return redirect(route('home'), 307, [
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}
}

