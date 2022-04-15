<?php

namespace App\Image;

use App\Models\SymLink;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Class FileDeleter.
 *
 * This class caches files for later deletion.
 * The typical usage is to subsequently add regular files and symlinks
 * via a series of calls to {@link FileDeleter::addRegularFile} or
 * {@link FileDeleter::addSymbolicLinks} and then delete all files at once
 * via {@link FileDeleter::do}.
 *
 * TODO: At the moment the file deleter uses string-encoded file path, eventually this class should use proper `File` objects
 */
class FileDeleter
{
	/**
	 * @var Collection<string>
	 */
	protected Collection $regularFiles;

	/**
	 * @var Collection<string>
	 */
	protected Collection $symbolicLinks;

	public function __construct()
	{
		$this->regularFiles = new Collection();
		$this->symbolicLinks = new Collection();
	}

	/**
	 * @param Collection<string> $regularFiles
	 *
	 * @return void
	 */
	public function addRegularFiles(Collection $regularFiles): void
	{
		$this->regularFiles = $this->regularFiles->merge($regularFiles);
	}

	/**
	 * @param Collection<string> $symbolicLinks
	 *
	 * @return void
	 */
	public function addSymbolicLinks(Collection $symbolicLinks): void
	{
		$this->symbolicLinks = $this->symbolicLinks->merge($symbolicLinks);
	}

	/**
	 * Deletes the collected files.
	 *
	 * @return bool
	 */
	public function do(): bool
	{
		$success = true;

		// TODO: When we use proper `File` objects, each file knows its associated disk
		// In the mean time, we assume that any regular file is stored on the default disk.
		$defaultDisk = Storage::disk();
		foreach ($this->regularFiles as $regularFile) {
			if ($defaultDisk->exists($regularFile)) {
				$success &= $defaultDisk->delete($regularFile);
			}
		}

		// TODO: When we use proper `File` objects, each file knows its associated disk
		// In the mean time, we assume that any symbolic link is stored on the same disk
		$symlinkDisk = Storage::disk(SymLink::DISK_NAME);
		foreach ($this->symbolicLinks as $symbolicLink) {
			$absolutePath = $symlinkDisk->path($symbolicLink);
			// Laravel and Flysystem does not support symbolic links.
			// So we must use low-level methods here.
			$success &= ((is_link($absolutePath) && unlink($absolutePath)) || !file_exists($absolutePath));
		}

		return $success;
	}
}
