<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasCopyrightTrait
{
	protected ?string $copyright = null;

	/**
	 * @return string|null
	 */
	public function copyright(): ?string
	{
		return $this->copyright;
	}
}
