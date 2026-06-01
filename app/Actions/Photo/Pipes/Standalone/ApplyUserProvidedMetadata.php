<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;

/**
 * Apply user-supplied title and description to the photo before EXIF hydration.
 *
 * When a caller explicitly provides a title or description at upload time
 * (FR-041-01, FR-041-02), those values are written to the photo model here
 * so that {@link \App\Actions\Photo\Pipes\Shared\HydrateMetadata} — which only
 * overwrites null fields — will then leave them untouched.
 *
 * This pipe is a no-op when the values are absent (FR-041-05, FR-041-03).
 * For duplicate uploads this pipe never runs; the duplicate keeps its existing
 * title and description.
 */
class ApplyUserProvidedMetadata implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		if ($state->title !== null) {
			$state->photo->title = $state->title;
		}

		if ($state->description !== null) {
			$state->photo->description = $state->description;
		}

		return $next($state);
	}
}
