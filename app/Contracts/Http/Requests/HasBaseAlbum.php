<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use App\Models\Extensions\BaseAlbum;

interface HasBaseAlbum extends HasAbstractAlbum
{
	/**
	 * @return BaseAlbum|null
	 */
	public function album(): ?BaseAlbum;
}
