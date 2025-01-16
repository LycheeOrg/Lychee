<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasUserIdTrait
{
	/**
	 * @var int
	 */
	protected int $userId;

	/**
	 * @return int
	 */
	public function userId(): int
	{
		return $this->userId;
	}
}
