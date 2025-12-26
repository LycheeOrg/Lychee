<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\ColourExtractor;

use App\Contracts\Image\ColourPaletteExtractorInterface;
use App\Image\Files\FlysystemFile;
use App\Repositories\ConfigManager;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;

class FarzaiExtractor implements ColourPaletteExtractorInterface
{
	public function __construct(
		private ConfigManager $config_manager)
	{
	}

	/**
	 * @return 'gd'|'imagick'
	 */
	private function getImageHandler(): string
	{
		if ($this->config_manager->hasImagick()) {
			return 'imagick';
		}

		return 'gd';
	}

	/**
	 * @return string[]
	 */
	public function extract(FlysystemFile $file): array
	{
		$image_handler = $this->getImageHandler();
		// Create an image instance
		$image = ImageFactoryForColourExtraction::createFromFile($file, $image_handler);

		// Extract colours
		$extractor_factory = new ColorExtractorFactory();
		$extractor = $extractor_factory->make($image_handler);

		/** @var ColorPaletteInterface $colour_palette */
		$colour_palette = $extractor->extract($image, 5); // Extract 5 dominant colours

		/** @var array<int,ColorInterface> $colours */
		$colours = $colour_palette->getColors();

		return array_map(
			fn (ColorInterface $colour): string => $colour->toHex(),
			$colours
		);
	}
}