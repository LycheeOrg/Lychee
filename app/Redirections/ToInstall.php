<?php

namespace App\Redirections;

use App\Exceptions\InstallationException;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ToInstall implements Redirection
{
	/**
	 * @throws RouteNotFoundException
	 * @throws InstallationException
	 */
	public static function go(): RedirectResponse
	{
		// we remove installed.log in order to be able to access the install menu.
		$filename = base_path('installed.log');
		if (file_exists($filename)) {
			if (is_file($filename)) {
				try {
					unlink($filename);
				} catch (\Throwable $e) {
					throw new InstallationException('Could not remove ' . $filename, $e);
				}
			} else {
				throw new InstallationException('A filesystem object . ' . $filename . ' exists, but is not an ordinary file.');
			}
		}

		return redirect(route('install-welcome'), 307, [
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}
}

