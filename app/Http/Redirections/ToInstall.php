<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Redirections;

use App\Contracts\Http\Redirection;
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
	 * @throws BindingResolutionException
	 */
	public static function go(): RedirectResponse
	{
		return redirect(route('install-welcome'), Response::HTTP_TEMPORARY_REDIRECT, [
			'Cache-Control' => 'no-cache, must-revalidate',
		]);
	}
}
