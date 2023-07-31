<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

class ImageDimension extends Data
{
    #[Computed]
	public float $ratio;

	public function __construct(
		public int $width,
		public int $height
	) {
		$this->ratio = $this->height > 0 ? $this->width / $this->height : 0;
	}
}
