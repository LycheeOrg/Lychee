<?php

namespace App\Redirections;

use Illuminate\Http\RedirectResponse;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ToInstall implements Redirection
{
	/**
	 * @throws RouteNotFoundException
	 */
	public static function go(): RedirectResponse
	{
		// we remove installed.log in order to be able to access the install menu.
		unlink(base_path('installed.log'));

		return redirect(route('install-welcome'), 307, [
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}
}

