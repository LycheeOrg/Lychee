<?php

namespace App\DTO;

class ImageDimension extends DTO
{
	public int $width;
	public int $height;

	public function __construct(int $width, int $height)
	{
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'width' => $this->width,
			'height' => $this->height,
		];
	}
}
