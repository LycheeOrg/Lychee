<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo\Convert;

use App\Enum\ConvertableImageType;

class ImageTypeFactory
{
	public ?string $convertionClass = null;

	public function __construct(string $extension)
	{
		$this->convertionClass = match (true) {
			ConvertableImageType::isHeifImageType($extension) => 'HeifToJpeg',
			// TODO: Add more convertion types/classes
			default => null,
		};
	}

	public function make(): mixed
	{
		$class = 'App\Actions\Photo\Convert\\' . $this->convertionClass;

		return new $class();
	}
}