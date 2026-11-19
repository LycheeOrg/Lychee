<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

interface HasDateScrubber
{
	/**
	 * @return bool|null the per-album date scrubber override; null follows the global setting
	 */
	public function is_date_scrubber_enabled(): ?bool;

	/**
	 * Whether the request carried the field at all. Omitting it leaves the
	 * stored override unchanged (same contract as `published_at`, Feature
	 * 068), so clients that predate Feature 071 keep working.
	 */
	public function isDateScrubberEnabledProvided(): bool;
}
