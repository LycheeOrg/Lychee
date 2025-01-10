<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasAlbumIdsTrait
{
	/**
	 * @var string[]
	 */
	protected array $albumIds = [];

	/**
	 * @return string[]
	 */
	public function albumIds(): array
	{
		return $this->albumIds;
	}
}
