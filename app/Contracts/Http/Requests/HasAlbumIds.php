<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

interface HasAlbumIds
{
	/**
	 * @return string[]
	 */
	public function albumIds(): array;
}
