<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Pipes\Shared;

use App\Contracts\PhotoCreate\SharedPipe;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\Enum\UserUploadTrustLevel;
use App\Models\User;
use App\Repositories\ConfigManager;

/**
 * Determines and sets the `is_upload_validated` flag on the photo being created.
 *
 * Rules (evaluated in order):
 *  1. If the intended owner exists and is an admin (`may_administrate`), always validated (true).
 *  2. If there is no authenticated owner (guest upload), use the `guest_upload_trust_level` config.
 *  3. Otherwise use the owner's `upload_trust_level`.
 *
 * Trust level mapping:
 *  - CHECK   → not validated (false)
 *  - MONITOR → validated (true)  — behaves as TRUSTED in this iteration
 *  - TRUSTED → validated (true)
 */
class SetUploadValidated implements SharedPipe
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
	}

	public function handle(DuplicateDTO|StandaloneDTO $state, \Closure $next): DuplicateDTO|StandaloneDTO
	{
		$state->photo->is_upload_validated = $this->resolveIsValidated($state->intended_owner_id, $state->is_guest_upload);

		return $next($state);
	}

	private function resolveIsValidated(int $intended_owner_id, bool $is_guest_upload = false): bool
	{
		// Explicit guest upload flag (set by queued job when Auth::user() was null at dispatch time)
		// or legacy fallback: no authenticated owner means guest upload
		if ($is_guest_upload || $intended_owner_id === 0) {
			$trust_level = $this->config_manager->getValueAsEnum('guest_upload_trust_level', UserUploadTrustLevel::class)
				?? UserUploadTrustLevel::CHECK;

			return $trust_level !== UserUploadTrustLevel::CHECK;
		}

		$owner = User::find($intended_owner_id);

		// Owner not found → fail-open for backward compatibility
		if ($owner === null) {
			return true;
		}

		// Admin always bypasses trust level (Q-033-03 → A)
		if ($owner->may_administrate === true) {
			return true;
		}

		$trust_level = $owner->upload_trust_level;

		return $trust_level !== UserUploadTrustLevel::CHECK;
	}
}
