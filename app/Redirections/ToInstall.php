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
			return redirect(route('install-welcome'), Response::HTTP_TEMPORARY_REDIRECT, [
				'Cache-Control' => 'no-cache, must-revalidate',
			]);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
