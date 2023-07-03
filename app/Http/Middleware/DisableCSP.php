<?php

namespace App\Http\Middleware;

use App\Contracts\Exceptions\LycheeException;
use Illuminate\Http\Request;

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
		if (
			config('debugbar.enabled', false) === true ||
			$request->getRequestUri() === '/docs/api'
		) {
			config(['secure-headers.csp.enable' => false]);
		}

		if ($request->getRequestUri() === '/' . config('log-viewer.route_path', 'Logs')) {
			// We must disable unsafe-eval because vue3 used by log-viewer requires it.
			// We must disable unsafe-inline (and hashes) because log-viewer uses inline script with parameter to boot.
			// Those parameters are not know by Lychee if someone modifies the config.
			// We only do that in that specific case. It is disabled by default otherwise.
			config(['secure-headers.csp.script-src.unsafe-eval' => true]);
			config(['secure-headers.csp.script-src.unsafe-inline' => true]);
			config(['secure-headers.csp.script-src.hashes.sha256' => []]);
		}

		if (config('app.livewire',false) === true) {
			// We have to disable unsafe-eval because Livewire requires it...
			// So studpid.
			config(['secure-headers.csp.script-src.unsafe-eval' => true]);
		}

		return $next($request);
	}
}
