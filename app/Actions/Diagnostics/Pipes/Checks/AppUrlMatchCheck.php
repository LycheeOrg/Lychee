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
		if (config('app.url') !== request()->httpHost()) {
			$data[] = 'Error: APP_URL does not match the current url. This will break U2F authentication. Please update APP_URL to reflect this change.';
		}

		return $next($data);
	}
}