<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use App\Models\Album;

interface HasFromAlbum extends HasBaseAlbum
{
	/**
	 * @return Album|null
	 */
	public function from_album(): ?Album;
}
