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

	/**
	 * @var Collection<string>
	 */
	protected Collection $regularFilesOrSymbolicLinks;

	public function __construct()
	{
		$this->regularFiles = new Collection();
		$this->symbolicLinks = new Collection();
		$this->regularFilesOrSymbolicLinks = new Collection();
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
	 * @param Collection<string> $regularFilesOrSymbolicLinks
	 *
	 * @return void
	 */
	public function addRegularFilesOrSymbolicLinks(Collection $regularFilesOrSymbolicLinks): void
	{
		$this->regularFilesOrSymbolicLinks = $this->regularFilesOrSymbolicLinks->merge($regularFilesOrSymbolicLinks);
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

		// If the disk uses the local driver, we use low-level routines as
		// these are also able to handle symbolic links in case of doubt
		$isLocalDisk = ($defaultDisk->getDriver()->getAdapter() instanceof \League\Flysystem\Adapter\Local);
		if ($isLocalDisk) {
			foreach ($this->regularFilesOrSymbolicLinks as $fileOrLink) {
				$absolutePath = $defaultDisk->path($fileOrLink);
				// This seemingly complicated and redundant statement uses
				// lazy evaluation and avoid errors for non-existing files.
				// Also note, the `file_exist` returns `false` for existing,
				// but dead links.
				// So the first part takes care of deleting links no matter
				// if they are dead or alive.
				// The latter part deletes (regular) files, but avoids errors
				// in case the file doesn't exist.
				$success &= ((is_link($absolutePath) && unlink($absolutePath)) || !file_exists($absolutePath) || unlink($absolutePath));
			}
		} else {
			// If the disk is no local, we can assume that each file is a regular file
			foreach ($this->regularFilesOrSymbolicLinks as $regularFile) {
				if ($defaultDisk->exists($regularFile)) {
					$success &= $defaultDisk->delete($regularFile);
				}
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
