<?php

namespace App\Image;

use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use Illuminate\Http\UploadedFile;

/**
 * Class MediaFile.
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
	public const SUPPORTED_PHP_EXIF_IMAGE_TYPES = [
		IMAGETYPE_GIF,
		IMAGETYPE_JPEG,
		IMAGETYPE_PNG,
		IMAGETYPE_WEBP,
	];

	public const SUPPORTED_IMAGE_FILE_EXTENSIONS = [
		'.jpg',
		'.jpeg',
		'.png',
		'.gif',
		'.webp',
	];

	public const SUPPORTED_VIDEO_FILE_EXTENSIONS = [
		'.avi',
		'.m4v',
		'.mov',
		'.mp4',
		'.mpg',
		'.ogv',
		'.webm',
		'.wmv',
	];

	public const SUPPORTED_IMAGE_MIME_TYPES = [
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/webp',
	];

	public const SUPPORTED_VIDEO_MIME_TYPES = [
		'video/mp4',
		'video/mpeg',
		'image/x-tga', // mpg; will be corrected by the metadata extractor
		'video/ogg',
		'video/webm',
		'video/quicktime',
		'video/x-ms-asf', // wmv file
		'video/x-ms-wmv', // wmv file
		'video/x-msvideo', // Avi
		'video/x-m4v', // Avi
		'application/octet-stream', // Some mp4 files; will be corrected by the metadata extractor
	];

	/** @var ?resource */
	protected $stream = null;

	/**
	 * Returns a resource from which can be read.
	 *
	 * To free the resource after use, call {@link MediaFile::close()}.
	 *
	 * @return resource
	 *
	 * @throws MediaFileOperationException
	 * @throws LycheeLogicException
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
	 *
	 * @throws MediaFileOperationException
	 * @throws LycheeLogicException
	 */
	abstract public function write($stream): void;

	/**
	 * Closes the internal stream.
	 *
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
	 *
	 * @throws MediaFileOperationException
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
	 * Returns the original extension of the file.
	 *
	 * Normally, the original extension equals the extension.
	 * However, for temporary copies of downloaded or uploaded files the
	 * original extension is the extension as used by the source while the
	 * actual, physical extension is typically random.
	 *
	 * @return string
	 */
	public function getOriginalExtension(): string
	{
		return $this->getExtension();
	}

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

	/**
	 * Returns the original basename of the file.
	 *
	 * Normally, the original basename equals the basename.
	 * However, for temporary copies of downloaded or uploaded files the
	 * original basename is the basename as used by the source while the
	 * actual, physical basename is typically random.
	 *
	 * @return string
	 */
	public function getOriginalBasename(): string
	{
		return $this->getBasename();
	}

	/**
	 * Checks if the given MIME type designates a supported image type.
	 *
	 * @param string $mimeType the MIME type
	 *
	 * @return bool
	 */
	public static function isSupportedImageMimeType(string $mimeType): bool
	{
		return in_array($mimeType, self::SUPPORTED_IMAGE_MIME_TYPES, true);
	}

	/**
	 * Checks if the given MIME type designates a supported video type.
	 *
	 * @param string $mimeType the MIME type
	 *
	 * @return bool
	 */
	public static function isSupportedVideoMimeType(string $mimeType): bool
	{
		return in_array($mimeType, self::SUPPORTED_VIDEO_MIME_TYPES, true);
	}

	/**
	 * Checks if the given MIME type is supported.
	 *
	 * @param string $mimeType the MIME type
	 *
	 * @return bool
	 */
	public static function isSupportedMimeType(string $mimeType): bool
	{
		return
			self::isSupportedImageMimeType($mimeType) ||
			self::isSupportedVideoMimeType($mimeType);
	}

	/**
	 * Asserts that the given MIME type is supported.
	 *
	 * @param string $mimeType the MIME type
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 */
	public static function assertIsSupportedMimeType(string $mimeType): void
	{
		if (!self::isSupportedMimeType($mimeType)) {
			throw new MediaFileUnsupportedException(MediaFileUnsupportedException::DEFAULT_MESSAGE . ' (bad MIME type: ' . $mimeType . ')');
		}
	}

	/**
	 * Checks if the given file extension is a supported image extension.
	 *
	 * @param string $extension the file extension
	 *
	 * @return bool
	 */
	public static function isSupportedImageFileExtension(string $extension): bool
	{
		return in_array(strtolower($extension), self::SUPPORTED_IMAGE_FILE_EXTENSIONS, true);
	}

	/**
	 * Checks if the given file extension is a supported image extension.
	 *
	 * @param string $extension the file extension
	 *
	 * @return bool
	 */
	public static function isSupportedVideoFileExtension(string $extension): bool
	{
		return in_array(strtolower($extension), self::SUPPORTED_VIDEO_FILE_EXTENSIONS, true);
	}

	/**
	 * Checks if the given file extension is supported.
	 *
	 * @param string $extension the file extension
	 *
	 * @return bool
	 */
	public static function isSupportedFileExtension(string $extension): bool
	{
		return
			self::isSupportedImageFileExtension($extension) ||
			self::isSupportedVideoFileExtension($extension);
	}

	/**
	 * Asserts that the given extension is supported.
	 *
	 * @param string $extension the file extension
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 */
	public static function assertIsSupportedFileExtension(string $extension): void
	{
		if (!self::isSupportedFileExtension($extension)) {
			throw new MediaFileUnsupportedException(MediaFileUnsupportedException::DEFAULT_MESSAGE . ' (bad extension: ' . $extension . ')');
		}
	}
}
