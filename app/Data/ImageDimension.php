<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ImageDimension extends Data
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
