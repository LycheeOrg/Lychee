<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo\Convert;

use App\Contracts\Image\ConvertMediaFileInterface;
use App\Enum\ConvertableImageType;

class ImageTypeFactory
{
	public readonly ?string $conversionClass;

	public function __construct(string $extension)
	{
		$this->conversionClass = match (true) {
			ConvertableImageType::isHeifImageType($extension) => HeifToJpeg::class,
			// TODO: Add more convertion types/classes
			default => null,
		};
	}

	public function make(): ConvertMediaFileInterface
	{
		if ($this->conversionClass === null) {
			throw new \RuntimeException('No conversion class available for this file type');
		}

		$instance = new $this->conversionClass();

		return $instance;
	}
}