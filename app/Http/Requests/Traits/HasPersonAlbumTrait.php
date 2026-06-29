<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Models\PersonAlbum;

trait HasPersonAlbumTrait
{
	protected ?PersonAlbum $album = null;

	/**
	 * @return PersonAlbum|null
	 */
	public function album(): ?PersonAlbum
	{
		return $this->album;
	}
}
