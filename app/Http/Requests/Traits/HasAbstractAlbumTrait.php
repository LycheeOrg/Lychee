<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Contracts\Models\AbstractAlbum;

trait HasAbstractAlbumTrait
{
	protected ?AbstractAlbum $album = null;

	/**
	 * @return AbstractAlbum|null
	 */
	public function album(): ?AbstractAlbum
	{
		return $this->album;
	}
}
