<?php

namespace App\Actions\Import;

use App\Actions\Import\Extensions\Checks;
use App\Actions\Photo\Create;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\MassImportException;
use App\Exceptions\MediaFileOperationException;
use App\Image\MediaFile;
use App\Image\TemporaryLocalFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Collection;

class FromUrl
{
	use Checks;

	protected ExceptionHandler $exceptionHandler;

	/**
	 * @throws InsufficientFilesystemPermissions
	 */
	public function __construct()
	{
		// TODO: Why do we explicitly perform this check here? We don't check the other import classes. We could just let the import fail.
		// Moreover, we do not even use the `import` folder which is checked by this method.
		// There is similar odd test in {@link \App\Actions\Photo\Create::add()} which uses another "check" trait.
		$this->checkPermissions();
		$this->exceptionHandler = resolve(ExceptionHandler::class);
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
				$basename = pathinfo($path, PATHINFO_FILENAME);
				$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);

				// Validate photo extension even when `$create->add()` will do later.
				// This prevents us from downloading unsupported files.
				MediaFile::assertIsSupportedFileExtension($extension);

				// Download file, before exif checks the mimetype, otherwise we download it twice
				$tmpFile = new TemporaryLocalFile($extension, $basename);
				try {
					$downloadStream = fopen($url, 'r');
					$tmpFile->write($downloadStream);
					fclose($downloadStream);
				} catch (\Exception $e) {
					throw new MediaFileOperationException('Could not download ' . $url . ' to ' . $tmpFile->getAbsolutePath(), $e);
				}

				// Verify image
				// TODO: Consider to make this test a general part of \App\Actions\Photo\Create::add. Then we don't need those tests at multiple places.
				$tmpFile->assertIsSupported();

				// Import photo
				$result->add(
					$create->add($tmpFile, $album)
				);
			} catch (\Throwable $e) {
				$exceptions[] = $e;
				$this->exceptionHandler->report($e);
			}
		}

		if (count($exceptions) !== 0) {
			throw new MassImportException($exceptions);
		}

		return $result;
	}
}
