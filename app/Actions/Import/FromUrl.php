<?php

namespace App\Actions\Import;

use App\Actions\Photo\Create;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\Handler;
use App\Exceptions\MassImportException;
use App\Image\BaseMediaFile;
use App\Image\DownloadedFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use function Safe\parse_url;
use function Safe\set_time_limit;

class FromUrl
{
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
			Configs::getValueAsBool('skip_duplicates')
		));

		foreach ($urls as $url) {
			try {
				// Reset the execution timeout for every iteration.
				try {
					set_time_limit((int) ini_get('max_execution_time'));
				} catch (InfoException) {
					// Silently do nothing, if `set_time_limit` is denied.
				}

				// If the component parameter is specified, this function returns a string (or int in case of PHP_URL_PORT)
				/** @var string $path */
				$path = parse_url($url, PHP_URL_PATH);
				$extension = '.' . pathinfo($path, PATHINFO_EXTENSION);

				// Validate photo extension even when `$create->add()` will do later.
				// This prevents us from downloading unsupported files.
				BaseMediaFile::assertIsSupportedOrAcceptedFileExtension($extension);

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
