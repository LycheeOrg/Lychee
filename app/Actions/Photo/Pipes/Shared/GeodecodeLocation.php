<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\PhotoDTO;
use App\Contracts\PhotoCreate\PhotoPipe;
use App\Jobs\GeodecodeLocationJob;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Log;

/**
 * Dispatch an asynchronous job to reverse-geocode the GPS coordinates of a
 * photo and populate its location field.
 *
 * The actual HTTP call to Nominatim is intentionally deferred so that photo
 * uploads are not slowed down by the external network request.
 */
class GeodecodeLocation implements PhotoPipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO
	{
		if (!$this->config_manager->getValueAsBool('location_decoding')) {
			return $next($state);
		}

		$photo = $state->getPhoto();

		if ($photo->latitude === null || $photo->longitude === null) {
			return $next($state);
		}

		// @codeCoverageIgnoreStart
		// This is already tested directly in the GeodecodeLocationJobTest.
		try {
			GeodecodeLocationJob::dispatch($photo);
		} catch (\Exception $e) {
			// Fail silently and continue.
			Log::error('Failed to dispatch GeodecodeLocationJob: ' . $e->getMessage());
		}

		return $next($state);
		// @codeCoverageIgnoreEnd
	}
}
