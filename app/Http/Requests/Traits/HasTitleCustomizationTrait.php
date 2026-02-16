<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\AlbumTitleColor;
use App\Enum\AlbumTitlePosition;

trait HasTitleCustomizationTrait
{
	protected ?AlbumTitleColor $title_color = null;
	protected ?AlbumTitlePosition $title_position = null;

	/**
	 * @return AlbumTitleColor|null
	 */
	public function titleColor(): ?AlbumTitleColor
	{
		return $this->title_color;
	}

	/**
	 * @return AlbumTitlePosition|null
	 */
	public function titlePosition(): ?AlbumTitlePosition
	{
		return $this->title_position;
	}
}
