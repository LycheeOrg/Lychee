<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import;

use App\Actions\Import\Pipes\HasReporterTrait;
use App\DTO\FolderNode;
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
final class ExecNew
{
	use HasReporterTrait;

	/**
	 * @param ImportMode $import_mode       the import mode
	 * @param int        $intended_owner_id the intended owner ID for the imported photos and albums
	 */
	public function __construct(
		private ImportMode $import_mode,
		private int $intended_owner_id)
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
			$root = new FolderNode(
				name: 'root', // Does not matter, as the initial node is going to be replaced.
				path: $path
			);

			$import_photo = new ImportDTO(
				intended_owner_id: $this->intended_owner_id,
				import_mode: $this->import_mode,
				parent_album: $parent_album,
				root_folder: $root,
			);

			set_time_limit(ini_get('max_execution_time'));

			$pipes = [
				Pipes\BuildTree::class,
				Pipes\PruneEmptyNodes::class,
				Pipes\CreateNonExistingAlbums::class,
				Pipes\ImportPhotos::class,
			];

			app(Pipeline::class)
				->send($import_photo)
				->through($pipes)
				->thenReturn();

			$this->report(ImportEventReport::createWarning('complete', null, 'Import complete'));
		} catch (\Throwable $e) {
			$this->report(ImportEventReport::createFromException($e, null));
			throw $e;
		}
	}
}
