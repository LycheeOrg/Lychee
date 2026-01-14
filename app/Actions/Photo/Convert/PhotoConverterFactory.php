<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Convert;

use App\Contracts\PhotoCreate\PhotoConverter;
use App\Enum\ConvertableImageType;

class PhotoConverterFactory
{
	public function make(string $extension): ?PhotoConverter
	{
		return match (true) {
			ConvertableImageType::isHeifImageType($extension) => resolve(HeifToJpeg::class),
			default => null,
		};
	}
}