<?php

namespace App\Http\Middleware;

use App\Exceptions\SessionExpiredException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
	/**
	 * This middleware is used to reliably track session time of a logged-in user. It does so
	 * by storing a cookie containing timestamp of when a user logs in. This timestamp is reset each
	 * time an http request is sent by the user. If the difference between the
	 * timestamp stored in the cookie and current timestamp is larger than session.lifetime, this means that
	 * the user session has timed-out. At this point it forgets about this cookie and throws an
	 * {@link \App\Exceptions\SessionExpiredException}.
	 *
	 * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
	 */
	public function handle(Request $request, \Closure $next): Response
	{
		$timeout = config('session.lifetime') * 60;
		$lastActivity = Cookie::get('lastActivityTime');

		if (Auth::guest() && (!is_string($lastActivity) || $lastActivity === '')) {
			Cookie::queue(Cookie::forget('lastActivityTime'));
		}

		if (is_string($lastActivity) && $lastActivity !== '' && ((int) now()->timestamp - (int) $lastActivity > $timeout)) {
			Cookie::queue(Cookie::forget('lastActivityTime'));
			throw new SessionExpiredException();
		}

		if (Auth::check()) {
			cookie::queue('lastActivityTime', now()->timestamp, 0);
		}

		return $next($request);
	}
}
