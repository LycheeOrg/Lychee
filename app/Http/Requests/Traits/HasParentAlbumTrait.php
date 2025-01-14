<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Models\Album;

trait HasParentAlbumTrait
{
	protected ?Album $parent_album = null;

	/**
	 * @return Album|null
	 */
	public function parent_album(): ?Album
	{
		return $this->parent_album;
	}
}
