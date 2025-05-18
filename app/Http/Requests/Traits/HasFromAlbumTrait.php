<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Contracts\Models\AbstractAlbum;

trait HasFromAlbumTrait
{
	protected ?AbstractAlbum $from_album = null;

	/**
	 * @return AbstractAlbum|null
	 */
	public function from_album(): ?AbstractAlbum
	{
		return $this->from_album;
	}
}
