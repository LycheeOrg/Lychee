<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\FlySystemLycheeException;
use App\Exceptions\MediaFileOperationException;
use App\Image\StreamStat;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use function Safe\fclose;

/**
 * Class FlysystemFile.
 */
class FlysystemFile extends BaseMediaFile
{
	protected FilesystemAdapter $disk;
	protected string $relativePath;

	public function __construct(FilesystemAdapter $disk, string $relativePath)
	{
		$this->disk = $disk;
		$this->relativePath = $relativePath;
	}

	/**
	 * {@inheritDoc}
	 */
	public function read()
	{
		try {
			if (is_resource($this->stream)) {
				fclose($this->stream);
			}

			$this->stream = $this->disk->readStream($this->relativePath);
			if (!is_resource($this->stream)) {
				$this->stream = null;
				throw new FlySystemLycheeException('Filesystem::readStream failed');
			}
		} catch (\ErrorException|FilesystemException|FileNotFoundException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}

		return $this->stream;
	}

	/**
	 * {@inheritDoc}
	 */
	public function write($stream, bool $collectStatistics = false): ?StreamStat
	{
		try {
			$streamStat = $collectStatistics ? static::appendStatFilter($stream) : null;

			// The underlying Flysystem currently behaves inconsistent
			// with respect to whether it honors the umask value or not.
			// Hence, we explicitly set umask to zero to achieve consistent
			// behaviour.
			// Setting umask can be removed, after
			// https://github.com/thephpleague/flysystem/issues/1584
			// has been solved.
			// Also consider the warning in
			// https://www.php.net/manual/en/function.umask.php
			// regarding timing issues:
			// "Avoid using this function [...]. It is better to change the
			// file permissions with chmod() after creating the file. Using
			// umask() can lead to unexpected behavior of concurrently running
			// scripts and the webserver itself because they all use the same
			// umask."
			// However, Lychee cannot use `chmod`, because Flysystem may
			// also recursively create missing parent directories and/or
			// not be local.
			// This problem must be fixed on the library layer.
			$umask = \umask(0);
			if (!$this->disk->writeStream($this->relativePath, $stream)) {
				throw new FlySystemLycheeException('Filesystem::writeStream failed');
			}
			\umask($umask);

			return $streamStat;
		} catch (\ErrorException|FilesystemException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(): void
	{
		try {
			if (!$this->disk->delete($this->relativePath)) {
				throw new FlySystemLycheeException('Filesystem::delete failed');
			}
		} catch (\ErrorException|FilesystemException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function move(string $newPath): void
	{
		if (!$this->disk->move($this->relativePath, $newPath)) {
			throw new MediaFileOperationException('could not move file');
		}
		$this->relativePath = $newPath;
	}

	/**
	 * {@inheritDoc}
	 */
	public function exists(): bool
	{
		return $this->disk->exists($this->relativePath);
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastModified(): int
	{
		return $this->disk->lastModified($this->relativePath);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFilesize(): int
	{
		return $this->disk->size($this->relativePath);
	}

	/**
	 * Returns the relative path of the file wrt. the underlying Flysystem disk.
	 *
	 * @return string the relative path
	 */
	public function getRelativePath(): string
	{
		return $this->relativePath;
	}

	/**
	 * @return FilesystemAdapter the disk this file is stored on
	 */
	public function getDisk(): FilesystemAdapter
	{
		return $this->disk;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExtension(): string
	{
		$ext = pathinfo($this->relativePath, PATHINFO_EXTENSION);

		return $ext !== '' ? '.' . $ext : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBasename(): string
	{
		return pathinfo($this->relativePath, PATHINFO_FILENAME);
	}

	/**
	 * Determines if this file is a local file.
	 *
	 * @return bool
	 */
	public function isLocalFile(): bool
	{
		return $this->disk->getAdapter() instanceof LocalFilesystemAdapter;
	}

	/**
	 * @throws MediaFileOperationException
	 */
	public function toLocalFile(): NativeLocalFile
	{
		if (!$this->isLocalFile()) {
			throw new MediaFileOperationException('file is not hosted locally');
		}

		return new NativeLocalFile($this->disk->path($this->relativePath));
	}
}
