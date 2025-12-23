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
use App\Image\Files\DownloadedFile;
use App\Models\Album;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use App\Services\Image\FileExtensionService;
use Illuminate\Support\Collection;
use LycheeVerify\Contract\VerifyInterface;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use function Safe\parse_url;
use function Safe\set_time_limit;

class FromUrl
{
	public function __construct(
		protected readonly VerifyInterface $verify,
		protected readonly ConfigManager $config_manager,
		protected readonly FileExtensionService $file_extension_service,
	) {
	}

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
			verify: $this->verify,
			file_extension_service: $this->file_extension_service,
			import_mode: new ImportMode(
				delete_imported: true,
				skip_duplicates: $this->config_manager->getValueAsBool('skip_duplicates'),
				shall_rename_photo_title: $this->config_manager->getValueAsBool('renamer_photo_title_enabled'),
			),
			intended_owner_id: $intended_owner_id
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
					$this->file_extension_service->assertIsSupportedOrAcceptedFileExtension($extension);
				}

				// Download file
				$downloaded_file = new DownloadedFile(
					$this->config_manager,
					$this->file_extension_service,
					$url
				);

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