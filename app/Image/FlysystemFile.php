<?php

namespace App\Image;

use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use Illuminate\Contracts\Filesystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;

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
		if (is_resource($this->stream)) {
			throw new LycheeLogicException('Stream is already opened for read');
		}
		try {
			$this->stream = $this->disk->readStream($this->relativePath);
			if ($this->stream === false || !is_resource($this->stream)) {
				$this->stream = null;
				throw new MediaFileOperationException('readStream failed');
			}
		} catch (\Throwable $e) {
			throw new MediaFileOperationException('Could not read from file ' . $this->relativePath, $e);
		}

		return $this->stream;
	}

	/**
	 * {@inheritDoc}
	 */
	public function write($stream): void
	{
		if (is_resource($this->stream)) {
			throw new LycheeLogicException('Cannot write to a file which is opened for read');
		}
		try {
			// TODO: `put` must be replaced by `writeStream` when Flysystem 2 is shipped with Laravel 9
			// This will also be more consistent with `readStream`.
			// Note that v1 also provides a method `writeStream`, but this is a misnomer.
			// See: https://flysystem.thephpleague.com/v2/docs/what-is-new/
			if (!$this->disk->put($this->relativePath, $stream)) {
				throw new MediaFileOperationException('put returned false');
			}
		} catch (\Throwable $e) {
			throw new MediaFileOperationException('Could not write to file ' . $this->relativePath, $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(): void
	{
		if (!$this->disk->delete($this->relativePath)) {
			throw new MediaFileOperationException('Could not delete file ' . $this->relativePath);
		}
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
	 * @return AdapterInterface
	 */
	public function getStorageAdapter(): AdapterInterface
	{
		return $this->disk->getDriver()->getAdapter();
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
	 * @throws MediaFileOperationException
	 */
	public function toLocalFile(): NativeLocalFile
	{
		if (!($this->disk->getDriver()->getAdapter() instanceof LocalAdapter)) {
			throw new MediaFileOperationException('file is not hosted locally');
		}

		return new NativeLocalFile($this->getAbsolutePath());
	}
}
