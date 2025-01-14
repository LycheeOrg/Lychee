<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

trait HasUserIDsTrait
{
	/**
	 * @var array<int>
	 */
	protected array $userIDs = [];

	/**
	 * @return array<int>
	 */
	public function userIDs(): array
	{
		return $this->userIDs;
	}
}
