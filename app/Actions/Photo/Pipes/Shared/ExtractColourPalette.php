<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;
use App\Jobs\ExtractColoursJob;
use App\Models\Configs;

/**
 * Extract the colour palette from the image.
 */
class ExtractColourPalette implements PhotoPipe
{
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		if (!Configs::getValueAsBool('enable_colour_extractions')) {
			return $next($state);
		}

		// @codeCoverageIgnoreStart
		// This is already tested directly in the ExtractColoursJobTest.
		if (Configs::getValueAsBool('use_job_queues')) {
			ExtractColoursJob::dispatch($state->getPhoto());

			return $next($state);
		}

		$job = new ExtractColoursJob($state->getPhoto());
		$job->handle();

		return $next($state);
		// @codeCoverageIgnoreEnd
	}
}
