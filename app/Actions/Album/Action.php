<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Factories\AlbumFactory;

class Action
{
	protected AlbumFactory $albumFactory;

	public function __construct()
	{
		// instead of using DDI we resolve it. That way we can easily extend from action.
		$this->albumFactory = resolve(AlbumFactory::class);
	}
}
