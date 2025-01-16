<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasOwnerIdTrait
{
	protected ?int $owner_id = null;

	/**
	 * @return int|null
	 */
	public function ownerId(): ?int
	{
		return $this->owner_id;
	}
}
