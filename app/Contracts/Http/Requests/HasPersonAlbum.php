<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use App\Models\PersonAlbum;

interface HasPersonAlbum extends HasBaseAlbum
{
	/**
	 * @return PersonAlbum|null
	 */
	public function album(): ?PersonAlbum;
}
