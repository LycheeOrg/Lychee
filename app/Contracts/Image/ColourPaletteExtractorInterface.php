<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Image;

use App\Image\Files\FlysystemFile;

/**
 * Interface ColourPaletteExtractorInterface.
 */
interface ColourPaletteExtractorInterface
{
	/**
	 * Given a file (hopefully an image), this method extracts the dominant colours as an array of hex strings.
	 *
	 * @return string[]
	 */
	public function extract(FlysystemFile $file): array;
}
