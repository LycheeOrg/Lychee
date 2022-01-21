<?php

namespace App\Image;

/**
 * Interface MediaFile.
 *
 * This interface abstracts from the differences of files which are provided
 * through a Flysystem adapter and files outside Flysystem.
 */
abstract class MediaFile
{
	/** @var ?resource */
	protected $stream = null;

	/**
	 * Returns a resource from which can be read.
	 *
	 * To free the resource after use, call {@link MediaFile::close()}.
	 *
	 * @return resource
	 */
	abstract public function read();

	/**
	 * Writes the content of the provided resource into the file.
	 *
	 * Note, you must not write into a file which has been opened for
	 * reading via {@link MediaFile::read()} and not yet been closed again.
	 *
	 * @param resource $stream
	 *
	 * @return void
	 */
	abstract public function write($stream): void;

	/**
	 * @return void
	 */
	public function close(): void
	{
		if (is_resource($this->stream)) {
			fclose($this->stream);
			$this->stream = null;
		}
	}

	/**
	 * Deletes the file.
	 *
	 * @return void
	 */
	abstract public function delete(): void;

	/**
	 * Returns the absolute path of the file.
	 *
	 * @return string
	 */
	abstract public function getAbsolutePath(): string;

	/**
	 * Returns the extension of the file incl. a preceding dot.
	 *
	 * @return string
	 */
	abstract public function getExtension(): string;

	/**
	 * Returns the basename of the file.
	 *
	 * The basename of a file is the name of the file without any
	 * preceding path and without a file extension.
	 * For example, the basename of the file `/path/to/my-image.jpg` is
	 * `my-image`.
	 * Note, this terminology conflicts how the term "basename" is used in
	 * the PHP documentation.
	 *
	 * @return string
	 */
	abstract public function getBasename(): string;
}
