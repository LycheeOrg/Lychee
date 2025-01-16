<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Models\TagAlbum;

trait HasTagAlbumTrait
{
	protected ?TagAlbum $album = null;

	/**
	 * @return TagAlbum|null
	 */
	public function album(): ?TagAlbum
	{
		return $this->album;
	}
}
