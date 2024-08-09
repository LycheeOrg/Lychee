<?php

namespace App\Http\Middleware;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\UnauthenticatedException;
use App\Models\Configs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginRequired.
 *
 * This middleware is ensures that only logged in users can access Lychee.
 */
class LoginRequired
{
	public const ROOT = 'root';
	public const ALBUM = 'album';

	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request        the incoming request to serve
	 * @param \Closure $next           the next operation to be applied to the
	 *                                 request
	 * @param string   $requiredStatus the required login status; either
	 *                                 {@link self::ROOT} or
	 *                                 {@link self::ALBUM}
	 *
	 * @throws ConfigurationException
	 * @throws FrameworkException
	 */
	public function handle(Request $request, \Closure $next, string $requiredStatus): mixed
	{
		if (in_array($requiredStatus, [self::ALBUM, self::ROOT], true) === false) {
			throw new LycheeLogicException($requiredStatus . ' is not a valid login requirement.');
		}

		// We are logged in. Proceed.
		if (Auth::user() !== null) {
			return $next($request);
		}

		if (!Configs::getValueAsBool('login_required')) {
			// Login is not required. Proceed.
			return $next($request);
		}

		if ($requiredStatus === self::ALBUM && Configs::getValueAsBool('login_required_root_only')) {
			return $next($request);
		}

		throw new UnauthenticatedException('Login required.');
	}
}
