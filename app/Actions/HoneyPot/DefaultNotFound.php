<?php

namespace App\Actions\HoneyPot;

/**
 * Default error.
 */
class DefaultNotFound extends BasePipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(string $path, \Closure $next): never
	{
		$this->throwNotFound($path);
	}
}
