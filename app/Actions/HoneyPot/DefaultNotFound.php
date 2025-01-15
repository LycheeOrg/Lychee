<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

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
