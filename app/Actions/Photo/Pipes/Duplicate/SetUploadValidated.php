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
use App\Repositories\ConfigManager;

/**
 * Determines and sets the `is_validated` flag on the photo being created.
 *
 * Rules (evaluated in order):
 *  1. If the trust level was pre-resolved at dispatch time (e.g. from a queued job),
 *     use it directly — no DB lookup required.
 *  2. If there is no authenticated owner (guest upload, intended_owner_id === 0),
 *     use the `guest_upload_trust_level` config.
 *  3. If the intended owner is an admin (`may_administrate`), always validated.
 *  4. Otherwise use the owner's `upload_trust_level`.
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
		$state->photo->is_validated = $state->upload_trust_level !== UserUploadTrustLevel::CHECK;

		return $next($state);
	}
}
