<?php

namespace App\Http\Middleware;

use App\Facades\AccessControl;
use App\Models\Configs;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures that responses with empty content return status code 204.
 *
 * This fixes a bug in Laravel.
 */
class ProtectPublic
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request
	 * @param \Closure $next
	 *
	 * @return Response
	 *
	 * @throws \InvalidArgumentException
	 */
	public function handle(Request $request, Closure $next): Response
	{
		if (Configs::get_value('login_page_enable', '0') == '1') {
			$logged_in = AccessControl::is_logged_in();

			if ($logged_in === false) {
				if (!$request->is('/')) {
					return redirect('/');
				}
			}
		}

		return $next($request);
	}
}
