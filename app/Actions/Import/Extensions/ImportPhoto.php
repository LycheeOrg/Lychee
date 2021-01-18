<?php

namespace App\Actions\Import\Extensions;

use App\Actions\Photo\Create;
use Exception;

trait ImportPhoto
{
	/**
	 * Creates an array similar to a file upload array and adds the photo to Lychee.
	 *
	 * @param $path
	 * @param bool $delete_imported
	 * @param int  $albumID
	 * @param bool $force_skip_duplicates
	 *
	 * @return bool returns true when photo import was successful
	 */
	public function photo($path, $delete_imported, $albumID = 0, $force_skip_duplicates = false, $resync_metadata = false)
	{
		// No need to validate photo type and extension in this function.
		// $photo->add will take care of it.
		$mime = mime_content_type($path);

		$nameFile = [];
		$nameFile['name'] = $path;
		$nameFile['type'] = $mime;
		$nameFile['tmp_name'] = $path;

		$create = resolve(Create::class);

		try {
			if ($create->add($nameFile, $albumID, $delete_imported, $force_skip_duplicates, $resync_metadata) === false) {
				// @codeCoverageIgnoreStart
				return false;
				// @codeCoverageIgnoreEnd
			}
			// @codeCoverageIgnoreStart
		} catch (Exception $e) {
			return false;
		}
		// @codeCoverageIgnoreEnd

		return true;
	}
}
