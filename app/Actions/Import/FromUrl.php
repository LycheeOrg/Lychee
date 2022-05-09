<?php

namespace App\Actions\Import;

use App\Actions\Import\Extensions\Checks;
use App\Actions\Photo\Create;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\Handler;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\MassImportException;
use App\Image\DownloadedFile;
use App\Image\MediaFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Collection;

class FromUrl
{
	use Checks;

	/**
	 * @throws InsufficientFilesystemPermissions
	 */
	public function __construct()
	{
		// TODO: Why do we explicitly perform this check here? We don't check the other import classes. We could just let the import fail.
		// Moreover, we do not even use the `import` folder which is checked by this method.
		// There is similar odd test in {@link \App\Actions\Photo\Create::add()} which uses another "check" trait.
		$this->checkPermissions();
	}

	/**
	 * Imports photos from a list of URLs.
	 *
	 * TODO: Instead of returning a collection of photos and throwing a potential {@link MassImportException}, we should use a streamed response like in {@link FromServer}
	 *
	 * @param string[]   $urls
	 * @param Album|null $album
	 *
	 * @return Collection<Photo> the collection of imported photos
	 *
	 * @throws MassImportException
	 */
	public function do(array $urls, ?Album $album): Collection
	{
		$result = new Collection();
		$exceptions = [];
		$create = new Create(new ImportMode(
			true,
			Configs::get_value('skip_duplicates', '0') === '1'
		));

		foreach ($urls as $url) {
			try {
				// Reset the execution timeout for every iteration.
				set_time_limit(ini_get('max_execution_time'));

				$path = parse_url($url, PHP_URL_PATH);
				$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);

				// Validate photo extension even when `$create->add()` will do later.
				// This prevents us from downloading unsupported files.
				MediaFile::assertIsSupportedOrAcceptedFileExtension($extension);

				// Download file
				$downloadedFile = new DownloadedFile($url);

				// Import photo/video/raw
				$result->add($create->add($downloadedFile, $album));
			} catch (\Throwable $e) {
				$exceptions[] = $e;
				Handler::reportSafely($e);
			}
		}

		if (count($exceptions) !== 0) {
			throw new MassImportException($exceptions);
		}

		return $result;
	}
}
