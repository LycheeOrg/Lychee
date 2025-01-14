<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasPhotoIdsTrait
{
	/**
	 * @var string[]
	 */
	protected array $photoIds = [];

	/**
	 * @return string[]
	 */
	public function photoIds(): array
	{
		return $this->photoIds;
	}
}
