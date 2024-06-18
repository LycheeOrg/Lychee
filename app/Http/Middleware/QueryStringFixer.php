<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Source: https://github.com/livewire/livewire/discussions/3084#discussioncomment-935275.
 *
 * When working behind reverse proxy, mis-configuration may append the parts of the url as a query string.
 * We reverse this change here as it breaks the url validation later.
 */
class QueryStringFixer
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		if (isset($_SERVER['QUERY_STRING']) && Str::startsWith($_SERVER['QUERY_STRING'], '/')) {
			$parts = explode('&', htmlspecialchars_decode($_SERVER['QUERY_STRING']));
			array_shift($parts);
			$new = [];
			foreach ($parts as $v) {
				$x = explode('=', $v);
				$key = array_shift($x);
				$value = implode('=', $x);
				$new[$key] = $value;
			}
			$_SERVER['QUERY_STRING'] = http_build_query($new);
			$request->server->set('QUERY_STRING', $_SERVER['QUERY_STRING']);
		}

		return $next($request);
	}
}
