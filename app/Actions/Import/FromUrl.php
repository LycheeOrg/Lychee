<?php

namespace App\Actions\Import;

use App\Actions\Import\Extensions\Checks;
use App\Actions\Photo\Create;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Actions\Photo\Strategies\ImportMode;
use App\Image\TemporaryLocalFile;
use App\Models\Configs;
use App\Models\Logs;

class FromUrl
{
	use Constants;
	use Checks;

	public function __construct()
	{
		$this->checkPermissions();
	}

	public function do(array $urls, ?string $albumId): bool
	{
		$error = false;
		$create = new Create(new ImportMode(
			true,
			Configs::get_value('skip_duplicates', '0') === '1'
		));

		foreach ($urls as &$url) {
			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			$path = parse_url($url, PHP_URL_PATH);
			$basename = pathinfo($path, PATHINFO_FILENAME);
			$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);

			// Validate photo type and extension even when $this->photo (=> $photo->add) will do the same.
			// This prevents us from downloading invalid photos.
			// Verify extension
			if (!$this->isValidExtension($extension)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo format not supported (' . $url . ')');
				continue;
			}

			// Download file, before exif checks the mimetype, otherwise we download it twice
			$tmpFile = new TemporaryLocalFile();
			try {
				$downloadStream = fopen($url, 'r');
				$tmpFile->write($downloadStream);
				fclose($downloadStream);
			} catch (\Exception $e) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not download (' . $url . ') to (' . $tmpFile->getAbsolutePath() . ') due to ' . $e->getMessage());
				continue;
			}

			// Verify image
			// TODO: Consider to make this test a general part of \App\Actions\Photo\Create::add. Then we don't need those tests at multiple places.
			$type = @exif_imagetype($tmpFile->getAbsolutePath());
			if (!$this->isValidImageType($type) && !in_array(strtolower($extension), $this->validExtensions, true)) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Photo type not supported (' . $url . ')');
				continue;
			}

			// Import photo
			if ($create->add(SourceFileInfo::createByTempFile($basename, $extension, $tmpFile), $albumId) == null) {
				$error = true;
				Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $tmpFile->getAbsolutePath() . ')');
			}
		}

		return !$error;
	}
}
