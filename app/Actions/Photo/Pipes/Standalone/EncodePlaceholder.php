<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Exceptions\MediaFileOperationException;
use App\Image\PlaceholderEncoder;

class EncodePlaceholder implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		try {
			$placeholderEncoder = new PlaceholderEncoder();
			$placeholder = $state->getPhoto()->size_variants->getPlaceholder();
			if ($placeholder !== null) {
				$placeholderEncoder->do($placeholder);
			}

			return $next($state);
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Failed to encode placeholder to base64', $e);
		}
	}
}