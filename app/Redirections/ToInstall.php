<?php

namespace App\Redirections;

use App\Exceptions\InstallationFailedException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ToInstall implements Redirection
{
	/**
	 * @throws RouteNotFoundException
	 * @throws InstallationFailedException
	 * @throws FrameworkException
	 */
	public static function go(): RedirectResponse
	{
		try {
			// we remove installed.log in order to be able to access the install menu.
			$filename = base_path('installed.log');
			if (file_exists($filename)) {
				if (is_file($filename)) {
					try {
						unlink($filename);
					} catch (\Throwable $e) {
						throw new InstallationFailedException('Could not remove ' . $filename, $e);
					}
				} else {
					throw new InstallationFailedException('A filesystem object . ' . $filename . ' exists, but is not an ordinary file.');
				}
			}

			return redirect(route('install-welcome'), Response::HTTP_TEMPORARY_REDIRECT, [
				'Cache-Control' => 'no-cache, must-revalidate',
			]);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
