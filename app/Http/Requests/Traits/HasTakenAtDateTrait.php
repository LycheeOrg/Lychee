<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use Illuminate\Support\Carbon;

trait HasTakenAtDateTrait
{
	protected ?Carbon $taken_at = null;

	/**
	 * @return Carbon|null
	 */
	public function takenAt(): ?Carbon
	{
		return $this->taken_at;
	}
}