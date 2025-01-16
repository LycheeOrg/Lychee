<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Traits;

use App\Models\Album;

trait HasAlbumTrait
{
	protected ?Album $album = null;

	/**
	 * @return Album|null
	 */
	public function album(): ?Album
	{
		return $this->album;
	}
}
