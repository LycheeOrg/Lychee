<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import;

use App\Actions\Import\Pipes\HasReporterTrait;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\DTO\ImportMode;
use App\Models\Album;
use Illuminate\Pipeline\Pipeline;
use function Safe\ini_get;
use function Safe\set_time_limit;

/**
 * Class for handling improved directory import with tree-based album creation.
 */
final class Exec
{
	use HasReporterTrait;

	/**
	 * @param ImportMode $import_mode       the import mode
	 * @param int        $intended_owner_id the intended owner ID for the imported photos and albums
	 * @param bool       $delete_missing_photos whether to delete photos in the database that are not in the file system
	 * @param bool       $is_dry_run       whether to run in dry-run mode without making changes
	 */
	public function __construct(
		private ImportMode $import_mode,
		private int $intended_owner_id,
		private bool $delete_missing_photos = false,
		private bool $is_dry_run = false)
	{
	}

	/**
	 * Main method to execute the tree-based import.
	 *
	 * @param string     $path         Base path to import from
	 * @param Album|null $parent_album Optional parent album to import into
	 *
	 * @return void
	 *
	 * @throws \Throwable Any exception that occurs during the import process
	 */
	public function do(
		string $path,
		?Album $parent_album,
	): void {
		try {
			$import_photo = new ImportDTO(
				intended_owner_id: $this->intended_owner_id,
				import_mode: $this->import_mode,
				parent_album: $parent_album,
				path: $path,
				delete_missing_photos: $this->delete_missing_photos,
				is_dry_run: $this->is_dry_run,
			);

			set_time_limit(ini_get('max_execution_time'));

			$pipes = [
				Pipes\BuildTree::class,
				Pipes\PruneEmptyNodes::class,
				Pipes\CreateNonExistingAlbums::class,
				Pipes\DeleteMissingPhotos::class,
				Pipes\ImportPhotos::class,
			];

			app(Pipeline::class)
				->send($import_photo)
				->through($pipes)
				->thenReturn();

			$this->report(ImportEventReport::createInfo('complete', null, 'Import complete'));
		} catch (\Throwable $e) {
			$this->report(ImportEventReport::createFromException($e, null));
			throw $e;
		}
	}
}
