<?php

namespace App\Actions\Import;

use App\Actions\Photo\Create;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\Handler;
use App\Exceptions\MassImportException;
use App\Image\Files\DownloadedFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Support\Collection;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
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
	 * @param int        $intendedOwnerId
	 *
	 * @return Collection<Photo> the collection of imported photos
	 *
	 * @throws MassImportException
	 */
	public function do(array $urls, ?Album $album, int $intendedOwnerId): Collection
	{
		$result = new Collection();
		$exceptions = [];
		$create = new Create(
			new ImportMode(deleteImported: true, skipDuplicates: Configs::getValueAsBool('skip_duplicates')),
			$intendedOwnerId
		);

		foreach ($urls as $url) {
			try {
				// Reset the execution timeout for every iteration.
				try {
					set_time_limit((int) ini_get('max_execution_time'));
				} catch (InfoException) {
					// Silently do nothing, if `set_time_limit` is denied.
				}

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
