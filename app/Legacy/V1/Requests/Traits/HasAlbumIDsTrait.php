<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

/**
 * @codeCoverageIgnore Legacy stuff
 */
trait HasAlbumIDsTrait
{
	/**
	 * @var string[]
	 */
	protected array $albumIDs = [];

	/**
	 * @return string[]
	 */
	public function albumIDs(): array
	{
		return $this->albumIDs;
	}
}
