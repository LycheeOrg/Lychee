<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\Http\Controllers\SessionController;
use Closure;
use Illuminate\Http\Request;

class AlbumPWCheck
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$sess = SessionController::checkAccess($request);
		if ($sess == 0) {
			return response('false');
		}
		if ($sess == 1) {
			return $next($request);
		}
		if ($sess == 2) {
			return response('"Warning: Album private!"');
		}
		if ($sess == 3) {
			return response('"Warning: Wrong password!"');
		}
	}
}
