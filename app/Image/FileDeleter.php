<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image;

use App\Constants\FileSystem;
use App\Exceptions\Internal\FileDeletionException;
use App\Exceptions\MediaFileOperationException;
use App\Models\SizeVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use function Safe\unlink;

/**
 * Class FileDeleter.
 *
 * This class caches files for later deletion.
 * The typical usage is to subsequently add regular files and symlinks
 * via a series of calls to {@link FileDeleter::addSizeVariants},
 * {@link FileDeleter::addLivePhotoPaths} or
 * {@link FileDeleter::addSymbolicLinks} and then delete all files at once
 * via {@link FileDeleter::do}.
 */
class FileDeleter
{
	/**
	 * @param array<string,Collection<int,string>> $files
	 * @param Collection<int,SizeVariant>          $size_variants
	 * @param Collection<int,string>               $symbolic_links
	 *
	 * @return void
	 */
	public function __construct(
		protected array $files = [],
		protected Collection $size_variants = new Collection(),
		protected Collection $symbolic_links = new Collection(),
	) {
	}

	/**
	 * @param Collection<int,SizeVariant> $size_variants
	 */
	public function addSizeVariants(Collection $size_variants): void
	{
		$this->size_variants = $this->size_variants->merge($size_variants);
	}

	/**
	 * @param Collection<int,string> $symbolic_links
	 */
	public function addSymbolicLinks(Collection $symbolic_links): void
	{
		$this->symbolic_links = $this->symbolic_links->merge($symbolic_links);
	}

	/**
	 * Give the possility to add files with their associated storage to the deleter.
	 *
	 * @param Collection<int,string> $paths
	 */
	public function addFiles(Collection $paths, string $disk_name): void
	{
		$this->files[$disk_name] = ($this->files[$disk_name] ?? new Collection())->merge($paths);
	}

	/**
	 * Map the list of sizeVariants to their proper storage type for later processing.
	 *
	 * @return void
	 */
	private function convertSizeVariantsList()
	{
		/** @var Collection<string,Collection<int,SizeVariant>> $grouped */
		$grouped = $this->size_variants->groupBy('storage_disk');
		$grouped->each(
			fn (Collection $svs, string $k) => $this->files[$k] = ($this->files[$k] ?? new Collection())->merge($svs->pluck('short_path'))
		);
	}

	/**
	 * Deletes the collected files.
	 *
	 * @throws MediaFileOperationException
	 */
	public function do(): void
	{
		$first_exception = null;

		$this->convertSizeVariantsList();

		foreach ($this->files as $storage_type => $file_list) {
			$disk = Storage::disk($storage_type);

			// If the disk uses the local driver, we use low-level routines as
			// these are also able to handle symbolic links in case of doubt
			$is_local_disk = $disk->getAdapter() instanceof LocalFilesystemAdapter;
			if ($is_local_disk) {
				foreach ($file_list as $file) {
					try {
						$absolute_path = $disk->path($file);
						// Note, `file_exist` returns `false` for existing,
						// but dead links.
						// So the first part takes care of deleting links no matter
						// if they are dead or alive.
						// The latter part deletes (regular) files, but avoids errors
						// in case the file doesn't exist.
						if (is_link($absolute_path) || file_exists($absolute_path)) {
							unlink($absolute_path);
						}
					} catch (\Throwable $e) {
						$first_exception = $first_exception ?? $e;
					}
				}
			} else {
				// If the disk is not local, we can assume that each file is a regular file
				foreach ($file_list as $file) {
					try {
						if ($disk->exists($file)) {
							if (!$disk->delete($file)) {
								$first_exception = $first_exception ?? new FileDeletionException($storage_type, $file);
							}
						}
					} catch (\Throwable $e) {
						$first_exception = $first_exception ?? $e;
					}
				}
			}
		}

		// TODO: When we use proper `File` objects, each file knows its associated disk
		// In the mean time, we assume that any symbolic link is stored on the same disk
		$symlink_disk = Storage::disk(FileSystem::SYMLINK);
		foreach ($this->symbolic_links as $symbolic_link) {
			try {
				$absolute_path = $symlink_disk->path($symbolic_link);
				// Laravel and Flysystem does not support symbolic links.
				// So we must use low-level methods here.
				if (is_link($absolute_path) || file_exists($absolute_path)) {
					unlink($absolute_path);
				}
			} catch (\Throwable $e) {
				$first_exception = $first_exception ?? $e;
			}
		}

		if ($first_exception !== null) {
			throw new MediaFileOperationException('Could not delete some files', $first_exception);
		}
	}
}