<?php

namespace App\Contracts;

use App\Models\Photo;

/**
 * Interface SizeVariantNamingStrategy.
 */
abstract class SizeVariantNamingStrategy
{
	protected string $fallbackExtension = '';
	protected ?Photo $photo = null;

	public function setFallbackExtension(string $originalExtension)
	{
		$this->fallbackExtension = $originalExtension;
	}

	public function setPhoto(?Photo $photo)
	{
		$this->photo = $photo;
	}

	/**
	 * Generates a short path for the designated size variant.
	 *
	 * @param int $sizeVariant the size variant
	 *
	 * @return string The short path
	 */
	abstract public function generateShortPath(int $sizeVariant): string;
}
