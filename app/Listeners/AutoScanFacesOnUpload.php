<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\Events\PhotoSaved;
use App\Jobs\DispatchFaceScanJob;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Log;

/**
 * Automatically trigger face scanning when a photo is uploaded or updated,
 * if the AI Vision face scanning feature is enabled.
 */
class AutoScanFacesOnUpload
{
	/**
	 * Handle the PhotoSaved event.
	 *
	 * @param PhotoSaved $event
	 *
	 * @return void
	 */
	public function handle(PhotoSaved $event): void
	{
		$config_manager = app(ConfigManager::class);

		if ($config_manager->getValueAsString('ai_vision_enabled') !== '1') {
			return;
		}

		if ($config_manager->getValueAsString('ai_vision_face_enabled') !== '1') {
			return;
		}

		Log::info("AutoScanFacesOnUpload: dispatching face scan for photo {$event->photo_id}.");
		DispatchFaceScanJob::dispatch($event->photo_id);
	}
}
