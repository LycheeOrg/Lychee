<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Jobs\DispatchFaceScanJob;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Log;

/**
 * Automatically trigger face scanning when a photo is uploaded,
 * if the AI Vision face scanning feature is enabled.
 */
class AutoScanFacesOnUpload implements StandalonePipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// Process through the rest of the pipeline first
		$state = $next($state);

		// Check if AI vision is enabled
		if ($this->config_manager->getValueAsString('ai_vision_enabled') !== '1') {
			return $state;
		}

		// Check if face scanning is enabled
		if ($this->config_manager->getValueAsString('ai_vision_face_enabled') !== '1') {
			return $state;
		}

		// Dispatch face scanning job for the uploaded photo
		Log::info("AutoScanFacesOnUpload: dispatching face scan for photo {$state->photo->id}.");
		DispatchFaceScanJob::dispatch($state->photo->id);

		return $state;
	}
}
