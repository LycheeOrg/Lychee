<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

use Illuminate\Support\Carbon;

trait HasDateTrait
{
	protected ?Carbon $date = null;

	/**
	 * @return Carbon|null
	 */
	public function requestDate(): ?Carbon
	{
		return $this->date;
	}
}