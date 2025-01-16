<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

trait HasTagsTrait
{
	/**
	 * @var string[]
	 */
	protected array $tags = [];

	/**
	 * @return string[]
	 */
	public function tags(): array
	{
		return $this->tags;
	}
}
