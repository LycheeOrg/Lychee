<?php

namespace App\DTO;

class ImageDimension extends ArrayableDTO
{
	public function __construct(
		public int $width,
		public int $height
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
