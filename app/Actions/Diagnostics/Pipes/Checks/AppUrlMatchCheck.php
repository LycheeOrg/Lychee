<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;

class AppUrlMatchCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$config_url = config('app.url');
		// http:// is 7 characters.
		if (strpos($config_url, '/', 8) !== false) {
			$data[] = 'Warning: APP_URL contains a sub-path. This may impact your WebAuthn authentication.';
		} else if ($config_url !== request()->httpHost() && $config_url !== request()->schemeAndHttpHost()) {
			$data[] = 'Error: APP_URL does not match the current url. This will break WebAuthn authentication. Please update APP_URL to reflect this change.';
		}

		return $next($data);
	}
}
