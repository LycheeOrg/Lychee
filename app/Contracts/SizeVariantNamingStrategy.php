<?php

namespace App\Contracts;

use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Models\Photo;

/**
 * Interface SizeVariantNamingStrategy.
 */
abstract class SizeVariantNamingStrategy
{
	protected ?SourceFileInfo $sourceFileInfo = null;
	protected ?Photo $photo = null;

	public function setSourceFileInfo(?SourceFileInfo $sourceFileInfo)
	{
		$this->sourceFileInfo = $sourceFileInfo;
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
