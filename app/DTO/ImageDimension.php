<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

class ImageDimension extends ArrayableDTO
{
	public function __construct(
		public int $width,
		public int $height,
	) {
	}

	/**
	 * Return the ratio given width and height.
	 *
	 * @return float
	 */
	public function getRatio(): float
	{
		return $this->height > 0 ? $this->width / $this->height : 0;
	}
}
