<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

use App\Models\Extensions\BaseAlbum;

trait HasBaseAlbumTrait
{
	protected ?BaseAlbum $album = null;

	/**
	 * @return BaseAlbum|null
	 */
	public function album(): ?BaseAlbum
	{
		return $this->album;
	}
}
