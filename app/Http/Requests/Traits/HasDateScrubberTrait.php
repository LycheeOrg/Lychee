<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasDateScrubberTrait
{
	protected ?bool $is_date_scrubber_enabled = null;
	protected bool $is_date_scrubber_enabled_provided = false;

	/**
	 * @return bool|null the per-album date scrubber override; null follows the global setting
	 */
	public function is_date_scrubber_enabled(): ?bool
	{
		return $this->is_date_scrubber_enabled;
	}

	public function isDateScrubberEnabledProvided(): bool
	{
		return $this->is_date_scrubber_enabled_provided;
	}
}
