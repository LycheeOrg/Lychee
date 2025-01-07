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
	 * Handle an incoming request.
	 *
	 * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
	 */
	public function handle(Request $request, \Closure $next): Response
	{
		$timeout = config('session.lifetime') * 60;
		$lastActivity = Cookie::get('lastActivityTime');

		fwrite(STDOUT, print_r('Timeout: ', true) . $timeout . PHP_EOL);

		fwrite(STDOUT, print_r('Last activity:  ', true) . $lastActivity . PHP_EOL);
		fwrite(STDOUT, print_r('Current time:  ', true) . now()->timestamp . PHP_EOL);

		if (Auth::guest()) {
			Cookie::queue(Cookie::forget('lastActivityTime'));
		}

		if ($lastActivity && (now()->timestamp - $lastActivity > $timeout)) {
			Cookie::queue(Cookie::forget('lastActivityTime'));
			fwrite(STDOUT, print_r('Going to throw Session Expired Exception! ', true) . PHP_EOL);
			throw new SessionExpiredException();
		}

		if (Auth::check()) {
			cookie::queue('lastActivityTime', now()->timestamp, 0);
		}

		return $next($request);
	}
}
