<?php

namespace App\Http\Middleware;

use App\Exceptions\SessionExpiredException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimout
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

		fwrite(STDOUT, print_r('Last activity before Auth::guest(): ', true) . $lastActivity . PHP_EOL);

		if (Auth::guest()) {
			Cookie::queue(Cookie::forget('lastActivityTime'));
		}

		fwrite(STDOUT, print_r('Last activity after Auth::guest(): ', true) . $lastActivity . PHP_EOL);

		if ($lastActivity && (now()->timestamp - $lastActivity > $timeout)) {
			Cookie::queue(Cookie::forget('lastActivityTime'));
			fwrite(STDOUT, print_r('Throwing session expired exception', true) . PHP_EOL);
			throw new SessionExpiredException(SessionExpiredException::HTTP_LOGIN_TIMEOUT);
		}

		fwrite(STDOUT, print_r('Type of Last activity: ', true) . gettype($lastActivity) . PHP_EOL);

		if (Auth::check()) {
			cookie::queue('lastActivityTime', now()->timestamp, 0);
		}

		return $next($request);
	}
}
