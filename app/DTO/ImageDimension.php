<?php

namespace App\DTO;

class ImageDimension extends ArrayableDTO
{
	public function __construct(
		public int $width,
		public int $height
	) {
	}
}
