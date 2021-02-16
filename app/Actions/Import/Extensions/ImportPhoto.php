<?php

namespace App\Actions\Import\Extensions;

use App\Actions\Photo\Create;

trait ImportPhoto
{
	/**
	 * Creates an array similar to a file upload array and adds the photo to Lychee.
	 *
	 * @param $path
	 * @param bool $delete_imported
	 * @param bool $import_via_symlink
	 * @param int  $albumID
	 * @param bool $skip_duplicates
	 * @param bool $resync_metadata
	 *
	 * @return bool returns true when photo import was successful
	 */
	public function photo($path, $delete_imported, $import_via_symlink, $albumID = 0, $skip_duplicates = false, $resync_metadata = false)
	{
		// No need to validate photo type and extension in this function.
		// $photo->add will take care of it.
		$mime = mime_content_type($path);

		$nameFile = [];
		$nameFile['name'] = $path;
		$nameFile['type'] = $mime;
		$nameFile['tmp_name'] = $path;

		$create = resolve(Create::class);

		// avoid incompatible settings (delete originals takes precedence over symbolic links)
		if ($delete_imported) {
			$import_via_symlink = false;
		}
		// (re-syncing metadata makes no sense when importing duplicates)
		if (!$skip_duplicates) {
			$resync_metadata = false;
		}

		if ($create->add($nameFile, $albumID, $delete_imported, $skip_duplicates, $import_via_symlink, $resync_metadata) === false) {
			// @codeCoverageIgnoreStart
			return false;
			// @codeCoverageIgnoreEnd
		}

		return true;
	}
}
