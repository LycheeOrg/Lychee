<?php

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
		public string $baseName
	) {
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getOriginalBasename(): string
	{
		return $this->baseName;
	}
}
