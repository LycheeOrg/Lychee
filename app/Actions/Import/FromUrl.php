<?php

namespace App\Actions\Import;

use App\Actions\Import\Extensions\Checks;
use App\Actions\Import\Extensions\ImportPhoto;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Strategies\ImportMode;
use App\Facades\Helpers;
use App\Models\Logs;
use Illuminate\Support\Facades\Storage;

class FromUrl
{
	use Constants;
	use ImportPhoto;
	use Checks;

	public function __construct()
	{
		$this->checkPermissions();
	}

	public function do(array $urls, $albumId): bool
	{
		$error = false;

		foreach ($urls as &$url) {
			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			// Validate photo type and extension even when $this->photo (=> $photo->add) will do the same.
			// This prevents us from downloading invalid photos.
			// Verify extension
			$extension = Helpers::getExtension($url, true);
			if (!$this->isValidExtension($extension)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo format not supported (' . $url . ')');
				continue;
			}

			// Verify image
			$type = @exif_imagetype($url);
			if (!$this->isValidImageType($type) && !in_array(strtolower($extension), $this->validExtensions, true)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo type not supported (' . $url . ')');
				continue;
			}

			$filename = pathinfo($url, PATHINFO_FILENAME) . $extension;
			$tmp_name = Storage::path('import/' . $filename);
			if (@copy($url, $tmp_name) === false) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not copy file (' . $url . ') to temp-folder (' . $tmp_name . ')');
				continue;
			}

			// Import photo
			if (!$this->photo($tmp_name, $albumId, new ImportMode(true))) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $tmp_name . ')');
			}
		}

		return !$error;
	}
}
