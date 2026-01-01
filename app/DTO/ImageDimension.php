<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

final readonly class ImageDimension
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
