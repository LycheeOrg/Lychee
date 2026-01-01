<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasPhotoIdsTrait
{
	/**
	 * @var string[]
	 */
	protected array $photo_ids = [];

	/**
	 * @return string[]
	 */
	public function photoIds(): array
	{
		return $this->photo_ids;
	}
}
