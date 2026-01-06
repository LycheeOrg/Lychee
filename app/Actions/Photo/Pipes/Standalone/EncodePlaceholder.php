<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Exceptions\Handler;
use App\Image\PlaceholderEncoder;

class EncodePlaceholder implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		try {
			$placeholder_encoder = new PlaceholderEncoder();
			$placeholder = $state->getPhoto()->size_variants->getPlaceholder();
			if ($placeholder !== null) {
				$placeholder_encoder->do($placeholder);
			}
			// @codeCoverageIgnoreStart
		} catch (\Throwable $t) {
			// Don't re-throw the exception, because we do not want the
			// import to fail completely only due to missing size variants.
			// There are just too many options why the creation of size
			// variants may fail.
			Handler::reportSafely($t);
		}
		// @codeCoverageIgnoreEnd

		return $next($state);
	}
}
