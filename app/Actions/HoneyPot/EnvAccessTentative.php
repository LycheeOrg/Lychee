<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
