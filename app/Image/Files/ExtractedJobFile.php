<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

/**
 * Class ExtractedJobFile.
 *
 * Represents a local file which has been extracted from an Archive.
 * It does not hold content.
 */
readonly class ExtractedJobFile
{
	public function __construct(
		public string $path,
		public string $base_name,
	) {
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getOriginalBasename(): string
	{
		return $this->base_name;
	}
}
