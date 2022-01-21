<?php

namespace App\Image;

use Illuminate\Http\UploadedFile;

/**
 * Interface MediaFile.
 *
 * This interface abstracts from the differences of files which are provided
 * through a Flysystem adapter and files outside Flysystem.
 *
 * In particular, this abstraction provides a unified copy-mechanism
 * between different Flysystem disks, local (native) files and uploaded files
 * via
 *
 *     $targetFile->write($sourceFile->read())
 *
 * using streams.
 * This stream-based approach is the same which is also used by
 * {@link UploadedFile::storeAs()} under the hood and avoids certain problems
 * which are may be caused by PHP method like `rename`, `move` or `copy`.
 * Firstly, these methods need a file path and thus do not work, if a file
 * resides on a Flysystem disk for which PHP has no native handler (e.g.
 * AWS S3 storage).
 * Secondly, `rename` struggles with filesystem permissions and ownership, if
 * the file is moved within the same path namespace but across mount points.
 * Copying via streams avoids issues like
 * [LycheeOrg/Lychee#1198](https://github.com/LycheeOrg/Lychee/issues/1198).
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
	 * @param resource $stream the input stream which provides the input to write
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
