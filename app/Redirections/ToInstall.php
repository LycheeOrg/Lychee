<?php

namespace App\Redirections;

use Illuminate\Http\RedirectResponse;

class ToInstall implements Redirection
{
	public static function go(): RedirectResponse
	{
		// we remove installed.log in order to be able to access the install menu.
		@unlink(base_path('installed.log'));

		return redirect(route('install-welcome'), 307, [
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}
}

