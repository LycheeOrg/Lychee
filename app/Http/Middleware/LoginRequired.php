<?php

namespace App\Http\Middleware;

use App\Assets\Features;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Configs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class LoginRequired.
 *
 * This middleware is ensures that only logged in users can access Lychee.
 */
class LoginRequired
{
	/**
	 * Handle an incoming request.
	 *
	 * @param Request  $request
	 * @param \Closure $next
	 *
	 * @return mixed
	 *
	 * @throws ConfigurationException
	 * @throws FrameworkException
	 */
	public function handle(Request $request, \Closure $next): mixed
	{
		$dir_url = config('app.dir_url');
		if (Features::inactive('livewire') && !Str::startsWith($request->getRequestUri(), $dir_url . '/livewire/')) {
			return $next($request);
		}

		try {
			if (!Configs::getValueAsBool('login_required')) {
				// Login is not required. Proceed.
				return $next($request);
			}

			if (Auth::user() !== null) {
				// We are logged in. Proceed.
				return $next($request);
			}

			return redirect()->route('login');
		} catch (ConfigurationKeyMissingException $e) {
			Log::warning(__METHOD__ . ':' . __LINE__ . ' ' . $e->getMessage());

			return $next($request);
		}
	}
}
