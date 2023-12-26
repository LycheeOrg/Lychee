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
		// https:// is 8 characters.
		if (strpos($config_url, '/', 8) !== false) {
			$data[] = 'Warning: APP_URL contains a sub-path. This may impact your WebAuthn authentication.';
		}

		if ($config_url !== request()->httpHost() && $config_url !== request()->schemeAndHttpHost()) {
			$data[] = 'Error: APP_URL does not match the current url. This will break WebAuthn authentication and prevent images to be properly displayed.';
		}

		$config_url_imgage = config('filesystems.disks.images.url');
		if ($config_url_imgage === '') {
			$data[] = 'Error: LYCHEE_UPLOADS_URL is set and empty. This will prevent images to be displayed. Remove the line from your .env';
		}

		return $next($data);
	}
}
