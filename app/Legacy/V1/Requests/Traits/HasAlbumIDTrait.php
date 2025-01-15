<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

trait HasAlbumIDTrait
{
	protected ?string $albumID = null;

	/**
	 * @return string|null
	 */
	public function albumID(): ?string
	{
		return $this->albumID;
	}
}
