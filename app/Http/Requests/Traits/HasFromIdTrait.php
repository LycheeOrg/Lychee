<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasFromIdTrait
{
	protected ?string $from_id = null;

	/**
	 * @return string|null
	 */
	public function from_id(): ?string
	{
		return $this->from_id;
	}
}