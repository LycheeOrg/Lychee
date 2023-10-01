<?php

namespace App\Actions\HoneyPot;

/**
 * Access if Honey pot is active.
 * If not we immediately throw a normal 404 exception.
 */
class HoneyIsActive extends BasePipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(string $path, \Closure $next): never
	{
		if (config('honeypot.enabled', true) !== true) {
			$this->throwNotFound($path);
		}

		$next($path);
	}
}
