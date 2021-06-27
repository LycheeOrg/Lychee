<?php

namespace App\Actions\Import\Extensions;

use App\Actions\Photo\Create;
use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Actions\Photo\Strategies\ImportMode;

trait ImportPhoto
{
	/**
	 * Creates an array similar to a file upload array and adds the photo to Lychee.
	 *
	 * @param string          $path
	 * @param int             $albumID
	 * @param ImportMode|null $importMode
	 *
	 * @return bool returns true when photo import was successful
	 */
	public function photo(
		string $path,
		int $albumID = 0,
		?ImportMode $importMode = null): bool
	{
		// No need to validate photo type and extension in this function.
		// $create->add will take care of it.
		$sourceFileInfo = new SourceFileInfo($path, mime_content_type($path), $path);
		$create = new Create($importMode);
		$create->add($sourceFileInfo, $albumID);

		return true;
	}
}
