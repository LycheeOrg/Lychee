<?php

namespace App\Actions\Import;

use App\Actions\Import\Extensions\Checks;
use App\Actions\Photo\Create;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\MassImportException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Facades\Helpers;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FromUrl
{
	use Constants;
	use Checks;

	/**
	 * @throws InsufficientFilesystemPermissions
	 */
	public function __construct()
	{
		$this->checkPermissions();
	}

	/**
	 * @param string[]    $urls
	 * @param string|null $albumId
	 *
	 * @return Collection<Photo> the collection of imported photos
	 *
	 * @throws MassImportException
	 */
	public function do(array $urls, ?string $albumId): Collection
	{
		$result = new Collection();
		$exceptions = [];
		$create = new Create(new ImportMode(true));

		foreach ($urls as $url) {
			// Reset the execution timeout for every iteration.
			set_time_limit(ini_get('max_execution_time'));

			// Validate photo type and extension even when $this->photo (=> $photo->add) will do the same.
			// This prevents us from downloading invalid photos.
			// Verify extension
			$extension = Helpers::getExtension($url, true);
			if (!$this->isValidExtension($extension)) {
				$msg = 'Photo format not supported (' . $url . ')';
				$exceptions[] = new MediaFileUnsupportedException($msg);
				Logs::error(__METHOD__, __LINE__, $msg);
				continue;
			}

			// Verify image
			$type = exif_imagetype($url);
			if (!$this->isValidImageType($type) && !in_array(strtolower($extension), $this->validExtensions, true)) {
				$msg = 'Photo format not supported (' . $url . ')';
				$exceptions[] = new MediaFileUnsupportedException($msg);
				Logs::error(__METHOD__, __LINE__, $msg);
				continue;
			}

			$filename = pathinfo($url, PATHINFO_FILENAME) . $extension;
			$tmp_name = Storage::path('import/' . $filename);
			try {
				if (copy($url, $tmp_name) === false) {
					throw new \RuntimeException('copy returned false');
				}
			} catch (\Throwable $e) {
				$msg = 'Could not copy file (' . $url . ') to temp-folder (' . $tmp_name . ')';
				$exceptions[] = new MediaFileOperationException($msg, $e);
				Logs::error(__METHOD__, __LINE__, $msg);
				continue;
			}

			// Import photo
			try {
				$result->add(
					$create->add(SourceFileInfo::createForLocalFile($tmp_name), $albumId)
				);
			} catch (\Throwable $e) {
				$exceptions[] = $e;
				Logs::error(__METHOD__, __LINE__, 'Could not import file (' . $tmp_name . ')');
			}
		}

		if (count($exceptions) !== 0) {
			throw new MassImportException($exceptions);
		}

		return $result;
	}
}
