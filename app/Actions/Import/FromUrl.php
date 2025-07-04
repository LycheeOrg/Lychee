<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import;

use App\Actions\Photo\Create;
use App\DTO\ImportMode;
use App\Exceptions\Handler;
use App\Exceptions\MassImportException;
use App\Image\Files\BaseMediaFile;
use App\Image\Files\DownloadedFile;
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
	 * @param int        $intended_owner_id
	 *
	 * @return Collection<int,Photo> the collection of imported photos
	 *
	 * @throws MassImportException
	 */
	public function do(array $urls, ?Album $album, int $intended_owner_id): Collection
	{
		$result = new Collection();
		$exceptions = [];
		$create = new Create(
			new ImportMode(delete_imported: true, skip_duplicates: Configs::getValueAsBool('skip_duplicates')),
			$intended_owner_id
		);

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
				if ($extension !== '.') {
					// Validate photo extension even when `$create->add()` will do later.
					// This prevents us from downloading unsupported files.
					BaseMediaFile::assertIsSupportedOrAcceptedFileExtension($extension);
				}

				// Download file
				$downloaded_file = new DownloadedFile($url);

				// Import photo/video/raw
				$result->add($create->add($downloaded_file, $album));
				// @codeCoverageIgnoreStart
			} catch (\Throwable $e) {
				$exceptions[] = $e;
				Handler::reportSafely($e);
			}
			// @codeCoverageIgnoreEnd
		}

		if (count($exceptions) !== 0) {
			// @codeCoverageIgnoreStart
			// Those are already caught.
			throw new MassImportException($exceptions);
			// @codeCoverageIgnoreEnd
		}

		return $result;
	}
}