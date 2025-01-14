<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image;

use App\Exceptions\Internal\FileDeletionException;
use App\Exceptions\MediaFileOperationException;
use App\Models\SizeVariant;
use App\Models\SymLink;
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
	 * @param Collection<int,SizeVariant>          $sizeVariants
	 * @param Collection<int,string>               $symbolicLinks
	 *
	 * @return void
	 */
	public function __construct(
		protected array $files = [],
		protected Collection $sizeVariants = new Collection(),
		protected Collection $symbolicLinks = new Collection(),
	) {
	}

	/**
	 * @param Collection<int,SizeVariant> $sizeVariants
	 *
	 * @return void
	 */
	public function addSizeVariants(Collection $sizeVariants): void
	{
		$this->sizeVariants = $this->sizeVariants->merge($sizeVariants);
	}

	/**
	 * @param Collection<int,string> $symbolicLinks
	 *
	 * @return void
	 */
	public function addSymbolicLinks(Collection $symbolicLinks): void
	{
		$this->symbolicLinks = $this->symbolicLinks->merge($symbolicLinks);
	}

	/**
	 * Give the possility to add files with their associated storage to the deleter.
	 *
	 * @param Collection<int,string> $paths
	 * @param string                 $diskName
	 *
	 * @return void
	 */
	public function addFiles(Collection $paths, string $diskName): void
	{
		$this->files[$diskName] = ($this->files[$diskName] ?? new Collection())->merge($paths);
	}

	/**
	 * Map the list of sizeVariants to their proper storage type for later processing.
	 *
	 * @return void
	 */
	private function convertSizeVariantsList()
	{
		/** @var Collection<string,Collection<int,SizeVariant>> $grouped */
		$grouped = $this->sizeVariants->groupBy('storage_disk');
		$grouped->each(
			fn (Collection $svs, string $k) => $this->files[$k] = ($this->files[$k] ?? new Collection())->merge($svs->pluck('short_path'))
		);
	}

	/**
	 * Deletes the collected files.
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	public function do(): void
	{
		/** @var \Throwable|null $firstException */
		$firstException = null;

		$this->convertSizeVariantsList();

		foreach ($this->files as $storageType => $fileList) {
			$disk = Storage::disk($storageType);

			// If the disk uses the local driver, we use low-level routines as
			// these are also able to handle symbolic links in case of doubt
			$isLocalDisk = $disk->getAdapter() instanceof LocalFilesystemAdapter;
			if ($isLocalDisk) {
				foreach ($fileList as $file) {
					try {
						$absolutePath = $disk->path($file);
						// Note, `file_exist` returns `false` for existing,
						// but dead links.
						// So the first part takes care of deleting links no matter
						// if they are dead or alive.
						// The latter part deletes (regular) files, but avoids errors
						// in case the file doesn't exist.
						if (is_link($absolutePath) || file_exists($absolutePath)) {
							unlink($absolutePath);
						}
					} catch (\Throwable $e) {
						$firstException = $firstException ?? $e;
					}
				}
			} else {
				// If the disk is not local, we can assume that each file is a regular file
				foreach ($fileList as $file) {
					try {
						if ($disk->exists($file)) {
							if (!$disk->delete($file)) {
								$firstException = $firstException ?? new FileDeletionException($storageType, $file);
							}
						}
					} catch (\Throwable $e) {
						$firstException = $firstException ?? $e;
					}
				}
			}
		}

		// TODO: When we use proper `File` objects, each file knows its associated disk
		// In the mean time, we assume that any symbolic link is stored on the same disk
		$symlinkDisk = Storage::disk(SymLink::DISK_NAME);
		foreach ($this->symbolicLinks as $symbolicLink) {
			try {
				$absolutePath = $symlinkDisk->path($symbolicLink);
				// Laravel and Flysystem does not support symbolic links.
				// So we must use low-level methods here.
				if (is_link($absolutePath) || file_exists($absolutePath)) {
					unlink($absolutePath);
				}
			} catch (\Throwable $e) {
				$firstException = $firstException ?? $e;
			}
		}

		if ($firstException !== null) {
			throw new MediaFileOperationException('Could not delete some files', $firstException);
		}
	}
}