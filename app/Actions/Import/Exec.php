<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Import;

use App\Actions\Import\Pipes\HasReporterTrait;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\DTO\ImportMode;
use App\Enum\UserUploadTrustLevel;
use App\Image\Files\NativeLocalFile;
use App\Jobs\ImportImageJob;
use App\Jobs\RecomputeAlbumSizeJob;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use App\Models\User;
use App\Services\Image\FileExtensionService;
use Illuminate\Pipeline\Pipeline;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use function Safe\set_time_limit;

/**
 * Class for handling improved directory import with tree-based album creation.
 */
class Exec
{
	use HasReporterTrait;

	private ?UserUploadTrustLevel $upload_trust_level = null;

	/**
	 * @param ImportMode $import_mode           the import mode
	 * @param int        $intended_owner_id     the intended owner ID for the imported photos and albums
	 * @param bool       $delete_missing_photos whether to delete photos in the database that are not in the file system
	 * @param bool       $delete_missing_albums whether to delete albums in the database that are not in the file system
	 * @param bool       $is_dry_run            whether to run in dry-run mode without making changes
	 * @param bool       $should_execute_jobs   whether to dispatch the jobs at the end of the run
	 * @param ?string    $import_root           real import root each job re-checks its file against, `null` when unrestricted (FR-115-18)
	 * @param bool       $root_folder_as_album  whether the selected folder becomes an album (FR-115-09)
	 * @param bool       $scope_to_owner        whether album reuse and deletion only consider the owner's content (FR-115-12)
	 */
	public function __construct(
		private ImportMode $import_mode,
		private int $intended_owner_id,
		private bool $delete_missing_photos = false,
		private bool $delete_missing_albums = false,
		private bool $is_dry_run = false,
		private bool $should_execute_jobs = false,
		private ?string $import_root = null,
		private bool $root_folder_as_album = true,
		private bool $scope_to_owner = true,
	) {
	}

	/**
	 * Main method to execute the tree-based import.
	 *
	 * @param string     $path         Base path to import from
	 * @param Album|null $parent_album Optional parent album to import into
	 *
	 * @return ImportImageJob[]
	 *
	 * @throws \Throwable Any exception that occurs during the import process
	 */
	public function do(
		string $path,
		?Album $parent_album,
	): array {
		try {
			$import_photo = new ImportDTO(
				intended_owner_id: $this->intended_owner_id,
				import_mode: $this->import_mode,
				parent_album: $parent_album,
				path: $path,
				upload_trust_level: $this->uploadTrustLevel(),
				delete_missing_photos: $this->delete_missing_photos,
				delete_missing_albums: $this->delete_missing_albums,
				is_dry_run: $this->is_dry_run,
				should_execute_jobs: $this->should_execute_jobs,
				import_root: $this->import_root,
				root_folder_as_album: $this->root_folder_as_album,
				scope_to_owner: $this->scope_to_owner,
			);

			try {
				set_time_limit(intval(ini_get('max_execution_time')));
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			$pipes = [
				Pipes\BuildTree::class,
				Pipes\PruneEmptyNodes::class,
				Pipes\CreateNonExistingAlbums::class,
				Pipes\DeleteMissingAlbums::class,
				Pipes\DeleteMissingPhotos::class,
				Pipes\ImportPhotos::class,
				Pipes\ExecuteBatch::class,
			];

			/** @var ImportDTO $ret */
			$ret = app(Pipeline::class)
				->send($import_photo)
				->through($pipes)
				->thenReturn();

			$this->report(ImportEventReport::createInfo('complete', null, 'Import complete'));
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			$this->report(ImportEventReport::createFromException($e, null));
			throw $e;
		}
		// @codeCoverageIgnoreEnd

		return $ret->job_bus;
	}

	/**
	 * Import a list of individual files directly, bypassing tree-based album creation.
	 *
	 * Each file is validated for a supported extension and queued as an `ImportImageJob`.
	 * Files with unsupported extensions are skipped with a warning.
	 *
	 * @param string[]   $file_paths   Absolute paths to individual files to import
	 * @param Album|null $parent_album Optional parent album to import into (null = unsorted)
	 *
	 * @return (ImportImageJob|RecomputeAlbumSizeJob|RecomputeAlbumStatsJob)[]
	 */
	public function doFiles(array $file_paths, ?Album $parent_album): array
	{
		$job_bus = [];
		$file_extension_service = app(FileExtensionService::class);

		foreach ($file_paths as $file_path) {
			$extension = '.' . strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

			if (!$file_extension_service->isSupportedOrAcceptedFileExtension($extension)) {
				$this->report(ImportEventReport::createWarning('unsupported_extension', $file_path, 'Unsupported file extension skipped: ' . $extension));
				continue;
			}

			$file = new NativeLocalFile($file_path);
			$job_bus[] = new ImportImageJob($file, $this->intended_owner_id, $this->import_mode, $parent_album, $this->uploadTrustLevel(), $this->import_root);
			$this->report(ImportEventReport::createDebug('queued', $file_path, 'Queued file for import'));
		}

		if ($parent_album !== null && count($job_bus) > 0) {
			$job_bus[] = new RecomputeAlbumSizeJob($parent_album->id);
			$job_bus[] = new RecomputeAlbumStatsJob($parent_album->id);
		}

		if ($this->should_execute_jobs) {
			foreach ($job_bus as $job) {
				dispatch($job);
			}
		}

		return $job_bus;
	}

	/**
	 * Trust level of the imported photos, resolved once per run with the rule
	 * uploads use: admins are trusted, others get their own level (FR-115-08).
	 */
	private function uploadTrustLevel(): UserUploadTrustLevel
	{
		if ($this->upload_trust_level === null) {
			$owner = User::query()->findOrFail($this->intended_owner_id);
			$this->upload_trust_level = $owner->may_administrate ? UserUploadTrustLevel::TRUSTED : $owner->upload_trust_level;
		}

		return $this->upload_trust_level;
	}
}
