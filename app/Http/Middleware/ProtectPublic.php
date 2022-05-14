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
		$target = $next($request);
		if ((Configs::get_value('login_page_enable', '0') == '1')
		&& (!AccessControl::is_logged_in())
		&& (!$request->is('/'))) {
			return redirect('/');
		}

		return $target;
	}
}
