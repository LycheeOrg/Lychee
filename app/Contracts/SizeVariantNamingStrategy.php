<?php

namespace App\Contracts;

use App\Image\FlysystemFile;
use App\Models\Photo;

/**
 * Interface SizeVariantNamingStrategy.
 */
abstract class SizeVariantNamingStrategy
{
	protected string $fallbackExtension = '';
	protected ?Photo $photo = null;

	public function setFallbackExtension(string $fallbackExtension): void
	{
		$this->fallbackExtension = $fallbackExtension;
	}

	public function setPhoto(?Photo $photo): void
	{
		$this->photo = $photo;
	}

	/**
	 * Creates a file for the designated size variant.
	 *
	 * @param int $sizeVariant the size variant
	 *
	 * @return FlysystemFile the file
	 */
	abstract public function createFile(int $sizeVariant): FlysystemFile;

	/**
	 * Returns the default extension.
	 *
	 * @return string the default extension (incl. a preceding dot) which is
	 *                used by the naming strategy
	 */
	abstract public function getDefaultExtension(): string;
}
