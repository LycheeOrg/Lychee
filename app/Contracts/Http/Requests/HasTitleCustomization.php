<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

use App\Enum\AlbumTitleColor;
use App\Enum\AlbumTitlePosition;

interface HasTitleCustomization
{
	/**
	 * @return AlbumTitleColor|null
	 */
	public function titleColor(): ?AlbumTitleColor;

	/**
	 * @return AlbumTitlePosition|null
	 */
	public function titlePosition(): ?AlbumTitlePosition;
}
