<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Enum\PhotoLayoutType;

trait HasPhotoLayoutTrait
{
	protected ?PhotoLayoutType $photoLayout = null;

	/**
	 * @return PhotoLayoutType|null
	 */
	public function photoLayout(): ?PhotoLayoutType
	{
		return $this->photoLayout;
	}
}
