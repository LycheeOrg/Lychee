<?php

namespace App\Image;

use App\Exceptions\MediaFileOperationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Exception as FlyException;

/**
 * Class FlysystemFile.
 *
 * This class is based on legacy Flysystem v1 which ships with Laravel 8.
 * Laravel 9 will migrate to Flysystem v2 which provides a different and
 * more consistent API.
 *
 * For v1, this documentation is relevant:
 * https://flysystem.thephpleague.com/v1/docs/usage/filesystem-api/
 */
class FlysystemFile extends MediaFile
{
	protected Filesystem $disk;
	protected string $relativePath;

	public function __construct(Filesystem $disk, string $relativePath)
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
				\Safe\fclose($this->stream);
			}

			$this->stream = $this->disk->readStream($this->relativePath);
			if ($this->stream === false || !is_resource($this->stream)) {
				$this->stream = null;
				throw new FlyException('Filesystem::readStream failed');
			}
		} catch (\ErrorException|FlyException|FileNotFoundException $e) {
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

			// TODO: `put` must be replaced by `writeStream` when Flysystem 2 is shipped with Laravel 9
			// This will also be more consistent with `readStream`.
			// Note that v1 also provides a method `writeStream`, but this is a misnomer.
			// See: https://flysystem.thephpleague.com/v2/docs/what-is-new/
			if (!$this->disk->put($this->relativePath, $stream)) {
				throw new FlyException('Filesystem::put failed');
			}

			return $streamStat;
		} catch (\ErrorException|FlyException $e) {
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
				throw new FlyException('Filesystem::delete failed');
			}
		} catch (\ErrorException|FlyException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function move(string $newPath): void
	{
		if ($this->disk->move($this->relativePath, $newPath) === false) {
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
	 * Returns the absolute (aka "full") path of the Flysystem file.
	 *
	 * Note, the syntax of the absolute path depends on the adapter of the
	 * underlying Flysystem disk.
	 * For example, for a disk which uses the "Local" adapter, the absolute
	 * path starts with a slash `/`.
	 *
	 * Optimally, this method should not be used at all, because it exposes
	 * internal implementation details of the Flysystem adapter.
	 * However, it is a last resort to implement features which Flysystem does
	 * not provide using low-level functions.
	 *
	 * TODO: Remove it.
	 *
	 * See also: {@link FlysystemFile::getStorageAdapter()}.
	 *
	 * @return string
	 */
	public function getAbsolutePath(): string
	{
		return $this->disk->path($this->relativePath);
	}

	/**
	 * Returns the adapter which drives the Flysystem disk of the file.
	 *
	 * Correct interpretation of the absolute path of the file requires to
	 * know the "type" of the disk on which the file is located on.
	 *
	 * See also: {@link FlysystemFile::getAbsolutePath()}.
	 *
	 * TODO: Remove it.
	 *
	 * @return AdapterInterface
	 */
	public function getStorageAdapter(): AdapterInterface
	{
		return $this->disk->getDriver()->getAdapter();
	}

	/**
	 * @return Filesystem the disk this file is stored on
	 */
	public function getDisk(): Filesystem
	{
		return $this->disk;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExtension(): string
	{
		$ext = pathinfo($this->relativePath, PATHINFO_EXTENSION);

		return $ext ? '.' . $ext : '';
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
		return $this->disk->getDriver()->getAdapter() instanceof LocalAdapter;
	}

	/**
	 * @throws MediaFileOperationException
	 */
	public function toLocalFile(): NativeLocalFile
	{
		if (!$this->isLocalFile()) {
			throw new MediaFileOperationException('file is not hosted locally');
		}

		return new NativeLocalFile($this->getAbsolutePath());
	}
}
