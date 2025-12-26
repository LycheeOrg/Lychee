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
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Log;

/**
 * Extract the colour palette from the image.
 */
class ExtractColourPalette implements PhotoPipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		if (!$this->config_manager->getValueAsBool('enable_colour_extractions')) {
			return $next($state);
		}

		// @codeCoverageIgnoreStart
		// This is already tested directly in the ExtractColoursJobTest.
		try {
			ExtractColoursJob::dispatch($state->getPhoto());
		} catch (\Exception $e) {
			// Fail silently and continue.
			Log::error('Failed to ExtractColoursJob: ' . $e->getMessage());
		}

		return $next($state);
		// @codeCoverageIgnoreEnd
	}
}
