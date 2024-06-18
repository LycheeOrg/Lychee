<?php

declare(strict_types=1);

namespace App\Actions\HoneyPot;

use Illuminate\Support\Str;

/**
 * Anyone trying to access a .env file is not with good intentions.
 */
class EnvAccessTentative extends BasePipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(string $path, \Closure $next): never
	{
		if (Str::endsWith($path, '.env')) {
			$this->throwTeaPot($path);
		}

		$next($path);
	}
}
