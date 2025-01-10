<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Middleware;

use App\Assets\Features;
use App\Contracts\Exceptions\LycheeException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Class DisableCSP.
 *
 * This middleware disables the CSP when needed.
 * This ensures that some external dependencies are loaded for e.g.
 * docs/api or log-viewer
 */
class DisableCSP
{
	/**
	 * Handles an incoming request.
	 *
	 * @param Request  $request the incoming request to serve
	 * @param \Closure $next    the next operation to be applied to the
	 *                          request
	 *
	 * @return mixed
	 *
	 * @throws LycheeException
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		$dir_url = config('app.dir_url');
		if (
			config('debugbar.enabled', false) === true ||
			$request->getRequestUri() === $dir_url . '/docs/api' ||
			$request->getRequestUri() === $dir_url . '/request-docs'
		) {
			config(['secure-headers.csp.enable' => false]);
		}

		if ($request->getRequestUri() === $dir_url . '/' . config('log-viewer.route_path', 'Logs')) {
			// We must disable unsafe-eval because vue3 used by log-viewer requires it.
			// We must disable unsafe-inline (and hashes) because log-viewer uses inline script with parameter to boot.
			// Those parameters are not know by Lychee if someone modifies the config.
			// We only do that in that specific case. It is disabled by default otherwise.
			config(['secure-headers.csp.script-src.unsafe-eval' => true]);
			config(['secure-headers.csp.script-src.unsafe-inline' => true]);
			config(['secure-headers.csp.script-src.hashes.sha256' => []]);
		}

		// disable unsafe-eval if we are on a VueJS page
		if (Features::active('vuejs')) {
			$this->handleVueJS();
		}

		return $next($request);
	}

	/**
	 * Disabling rules because ... VueJS.
	 *
	 * @return void
	 *
	 * @throws BindingResolutionException
	 */
	private function handleVueJS()
	{
		// We have to disable unsafe-eval because Livewire requires it...
		// So stupid....
		config(['secure-headers.csp.script-src.unsafe-eval' => true]);

		// if the public/hot file exists, it means that we need to disable CSP completely
		// As we will be reloading on the fly the page and Vite has poor CSP support.
		if (File::exists(public_path('hot'))) {
			config(['secure-headers.csp.enable' => false]);
		}
	}
}
