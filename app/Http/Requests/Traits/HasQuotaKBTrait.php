<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasQuotaKBTrait
{
	/**
	 * @var ?int
	 */
	protected ?int $quota_kb;

	/**
	 * @return ?int
	 */
	public function quota_kb(): ?int
	{
		return $this->quota_kb;
	}
}
