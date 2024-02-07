<?php

namespace App\Image;

use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Enum\StorageDiskType;
use App\Exceptions\MediaFileOperationException;
use App\Models\SizeVariant;
use App\Models\SymLink;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use function Safe\unlink;

/**
 * Class FileDeleter.
 *
 * This class caches files for later deletion.
 * The typical usage is to subsequently add regular files and symlinks
 * via a series of calls to {@link FileDeleter::addSizeVariants},
 * {@link FileDeleter::addLivePhotoPaths} or
 *  {@link FileDeleter::addSymbolicLinks} and then delete all files at once
 * via {@link FileDeleter::do}.
 */
class FileDeleter
{
	/**
	 * @var Collection<SizeVariant>
	 */
	protected Collection $sizeVariants;

	/**
	 * @var Collection<string>
	 */
	protected Collection $symbolicLinks;

	/**
	 * @var Collection<string>
	 */
	protected Collection $livePhotoPaths;

	public function __construct()
	{
		$this->sizeVariants = new Collection();
	}

	/**
	 * @param Collection<SizeVariant> $sizeVariants
	 *
	 * @return void
	 */
	public function addSizeVariants(Collection $sizeVariants): void
	{
		$this->sizeVariants = $this->sizeVariants->merge($sizeVariants);
	}

	/**
	 * @param Collection<string> $symbolicLinks
	 * @return void
	 */
	public function addSymbolicLinks(Collection $symbolicLinks): void
	{
		$this->symbolicLinks = $this->symbolicLinks->merge($symbolicLinks);
	}

	/**
	 * @param Collection<string> $livePhotoPaths
	 * @return void
	 */
	public function addLivePhotoPaths(Collection $livePhotoPaths): void
	{
		$this->livePhotoPaths = $this->livePhotoPaths->merge($livePhotoPaths);
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

		foreach ($this->sizeVariants as $sizeVariant) {
			$fileDisk = Storage::disk($sizeVariant->storage_disk->value);
			try {
				if ($fileDisk->exists($sizeVariant->short_path)) {
					if (!$fileDisk->delete($sizeVariant->short_path)) {
						$firstException = $firstException ?? new \RuntimeException('Storage::delete failed: ' . $sizeVariant);
					}
				}
			} catch (\Throwable $e) {
				$firstException = $firstException ?? $e;
			}
		}

		foreach ($this->livePhotoPaths as $livePhotoPath) {
			// TODO How do we find out where a live photo is stored?
			$fileDisk = Storage::disk(StorageDiskType::LOCAL->value);
			try {
				if ($fileDisk->exists($livePhotoPath)) {
					if (!$fileDisk->delete($livePhotoPath)) {
						$firstException = $firstException ?? new \RuntimeException('Storage::delete failed: ' . $livePhotoPath);
					}
				}
			} catch (\Throwable $e) {
				$firstException = $firstException ?? $e;
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
			throw new MediaFileOperationException('Could not delete files', $firstException);
		}
	}
}