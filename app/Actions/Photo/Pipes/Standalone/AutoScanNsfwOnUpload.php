<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Enum\UserUploadTrustLevel;
use App\Jobs\DispatchNsfwScanJob;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\Log;

class AutoScanNsfwOnUpload implements StandalonePipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		// Step 1: Snapshot the uploader's trust level on the in-memory model.
		$state->photo->upload_trust_level = $state->upload_trust_level;

		// Step 2: Determine if scan will be dispatched.
		$should_scan = $this->shouldScan($state->upload_trust_level);

		// Step 3: Hide-on-scan — set is_validated = false before persistence if applicable.
		if ($should_scan && $this->shouldHideOnScan($state->upload_trust_level)) {
			$state->photo->is_validated = false;
		}
		$state->photo->save(); // Persist the changes to the photo model before dispatching the scan job.

		// Step 4: Let the pipeline persist the photo with upload_trust_level and is_validated set.
		$state = $next($state);

		// Step 5: Skip non-photos.
		if (!$state->photo->isPhoto()) {
			return $state;
		}

		// Step 6: Dispatch scan job after persistence.
		if ($should_scan) {
			Log::info("AutoScanNsfwOnUpload: dispatching NSFW scan for photo {$state->photo->id}.");
			DispatchNsfwScanJob::dispatch($state->photo->id);
		}

		return $state;
	}

	private function shouldScan(UserUploadTrustLevel $trust_level): bool
	{
		if (!$this->config_manager->getValueAsBool('ai_vision_enabled')) {
			return false;
		}

		if (!$this->config_manager->getValueAsBool('ai_vision_nsfw_enabled')) {
			return false;
		}

		if ($trust_level === UserUploadTrustLevel::TRUSTED) {
			return $this->config_manager->getValueAsBool('ai_vision_nsfw_scan_trusted_users');
		}

		return true;
	}

	private function shouldHideOnScan(UserUploadTrustLevel $trust_level): bool
	{
		return match ($trust_level) {
			UserUploadTrustLevel::MONITOR => $this->config_manager->getValueAsBool('ai_vision_nsfw_monitor_hide_on_scan'),
			UserUploadTrustLevel::TRUST_BUT_VERIFY => $this->config_manager->getValueAsBool('ai_vision_nsfw_trust_but_verify_hide_on_scan'),
			UserUploadTrustLevel::TRUSTED => $this->config_manager->getValueAsBool('ai_vision_nsfw_trust_hide_on_scan'),
			default => false, // CHECK users are already hidden via SetUploadValidated
		};
	}
}
