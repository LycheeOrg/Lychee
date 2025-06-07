<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\ColourExtractor;

use App\Contracts\Image\ColourPaletteExtractorInterface;
use App\Image\Files\FlysystemFile;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

class LeagueExtractor implements ColourPaletteExtractorInterface
{
	/**
	 * @return string[]
	 */
	public function extract(FlysystemFile $file): array
	{
		$image = ImageFactoryForColourExtraction::createGdResourceFromFile($file);
		$palette = Palette::fromGD($image);

		$extractor = new ColorExtractor($palette);
		$colors = $extractor->extract(5);

		return array_map(fn ($c) => Color::fromIntToHex($c), $colors);
	}
}