<?php

namespace App\Image;

use Illuminate\Http\UploadedFile;

/**
 * Class NativeLocalFile.
 *
 * Represents a file which must be handled with native PHP methods
 * like `fopen`, etc.
 * This mostly applies to files which are uploaded to the server or
 * imported from the server and thus are located outside any Flysystem disk.
 */
class NativeLocalFile extends MediaFile
{
	protected string $absolutePath;

	public function __construct(string $path)
	{
		$absolutePath = realpath($path);
		if ($absolutePath === false || !is_file($absolutePath)) {
			throw new \RuntimeException('The path "' . $path . '" does not point to a local file');
		}
		$this->absolutePath = $absolutePath;
	}

	public static function createFromUploadedFile(UploadedFile $file): self
	{
		$path = $file->getRealPath();
		if ($path === false) {
			throw new \RuntimeException('The uploaded file does not exist');
		}

		return new self($path);
	}

	/**
	 * {@inheritDoc}
	 */
	public function read()
	{
		if (is_resource($this->stream)) {
			throw new \LogicException('Cannot read from a file which is already opened for read');
		}
		$this->stream = fopen($this->absolutePath, 'rb');
		if ($this->stream === false || !is_resource($this->stream)) {
			$this->stream = null;
			throw new \RuntimeException('Could not read from file ' . $this->absolutePath);
		}

		return $this->stream;
	}

	/**
	 * {@inheritDoc}
	 */
	public function write($stream): void
	{
		if (is_resource($this->stream)) {
			throw new \LogicException('Cannot write to a file which is opened for read');
		}
		// inspired from \League\Flysystem\Adapter\Local
		$this->stream = fopen($this->absolutePath, 'wb');
		if (
			!is_resource($this->stream) ||
			stream_copy_to_stream($stream, $this->stream) === false ||
			!fclose($this->stream)
		) {
			throw new \RuntimeException('Could not write file ' . $this->absolutePath);
		}
		$this->stream = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(): void
	{
		if (!unlink($this->absolutePath)) {
			throw new \RuntimeException('Could not delete file ' . $this->absolutePath);
		}
	}

	/**
	 * @return string the absolute path of the file
	 */
	public function getAbsolutePath(): string
	{
		return $this->absolutePath;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExtension(): string
	{
		$ext = pathinfo($this->absolutePath, PATHINFO_EXTENSION);

		return $ext ? '.' . $ext : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBasename(): string
	{
		return pathinfo($this->absolutePath, PATHINFO_FILENAME);
	}
}
