<?php

// app/Http/Middleware/CacheControlMiddleware.php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class CacheControl
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request                                                                          $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
	 *
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, \Closure $next)
	{
		$response = $next($request);
		$response->headers->set('Cache-Control', 'private;max_age=3600');

		return $response;
	}
}