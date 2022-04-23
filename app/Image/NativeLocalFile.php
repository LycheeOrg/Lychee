<?php

namespace App\Image;

use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Models\Configs;

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
	protected ?string $cachedMimeType;

	/**
	 * @param string $path the file path
	 *
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $path)
	{
		$absolutePath = realpath($path);
		if ($absolutePath === false || !is_file($absolutePath)) {
			throw new MediaFileOperationException('The path "' . $path . '" does not point to a local file');
		}
		$this->absolutePath = $absolutePath;
		$this->cachedMimeType = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function read()
	{
		if (is_resource($this->stream)) {
			throw new LycheeLogicException('Cannot read from a file which is already opened for read');
		}
		$this->stream = fopen($this->absolutePath, 'rb');
		if ($this->stream === false || !is_resource($this->stream)) {
			$this->stream = null;
			throw new MediaFileOperationException('Could not read from file ' . $this->absolutePath);
		}

		return $this->stream;
	}

	/**
	 * {@inheritDoc}
	 *
	 * If new content is written to the file, the internally cached mime
	 * type is cleared.
	 * The mime type will be re-determined again upon the next invocation of
	 * {@link NativeLocalFile::getMimeType()}.
	 * This can be avoided by passing the MIME type of the stream.
	 *
	 * @param string|null $mimeType the mime type of `$stream`
	 */
	public function write($stream, ?string $mimeType = null): void
	{
		if (is_resource($this->stream)) {
			throw new LycheeLogicException('Cannot write to a file which is opened for read');
		}
		// inspired from \League\Flysystem\Adapter\Local
		$this->stream = fopen($this->absolutePath, 'wb');
		if (
			!is_resource($this->stream) ||
			stream_copy_to_stream($stream, $this->stream) === false ||
			!fclose($this->stream)
		) {
			throw new MediaFileOperationException('Could not write file ' . $this->absolutePath);
		}
		$this->stream = null;
		$this->cachedMimeType = $mimeType;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(): void
	{
		if (!unlink($this->absolutePath)) {
			throw new MediaFileOperationException('Could not delete file ' . $this->absolutePath);
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

	public function getMimeType(): string
	{
		if (!$this->cachedMimeType) {
			$this->cachedMimeType = mime_content_type($this->absolutePath);
		}

		return $this->cachedMimeType;
	}

	/**
	 * Returns the kind of media file.
	 *
	 * The kind is one out of:
	 *
	 *  - `'photo'` if the media file is a photo
	 *  - `'video'` if the media file is a video
	 *  - `'raw'` if the media file is an accepted file, but none of the other
	 *    two kinds (we only check extensions).
	 *
	 * @return string either `'photo'`, `'video'` or `'raw'`
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws ExternalComponentMissingException
	 */
	public function getFileKind(): string
	{
		$extension = $this->getOriginalExtension();
		// check raw files
		$raw_formats = strtolower(Configs::get_value('raw_formats', ''));
		if (in_array(strtolower($extension), explode('|', $raw_formats), true)) {
			return 'raw';
		}

		if (in_array(strtolower($extension), MediaFile::SUPPORTED_FILE_EXTENSIONS, true)) {
			$mimeType = $this->getMimeType();
			if (in_array($mimeType, MediaFile::SUPPORTED_VIDEO_MIME_TYPES, true)) {
				return 'video';
			}

			return 'photo';
		}

		// let's check for the mimetype
		// maybe we don't have a photo
		if (!function_exists('exif_imagetype')) {
			throw new ExternalComponentMissingException('EXIF library missing.');
		}

		$type = exif_imagetype($this->getAbsolutePath());
		if (in_array($type, MediaFile::SUPPORTED_PHP_EXIF_IMAGE_TYPES, true)) {
			return 'photo';
		}

		throw new MediaFileUnsupportedException('Photo type not supported: ' . $this->getOriginalBasename());
	}

	/**
	 * Checks if the file is a valid image type acc. to {@link MediaFile::SUPPORTED_PHP_EXIF_IMAGE_TYPES}.
	 *
	 * @return bool true, if the file has a valid EXIF type
	 */
	public function hasSupportedExifImageType(): bool
	{
		return in_array(exif_imagetype($this->getAbsolutePath()), self::SUPPORTED_PHP_EXIF_IMAGE_TYPES, true);
	}

	/**
	 * Determines whether the file is supported.
	 *
	 * @return bool true, if the file is supported
	 */
	public function isSupported(): bool
	{
		$mime = $this->getMimeType();
		$ext = $this->getOriginalExtension();

		return (
			self::isSupportedImageMimeType($mime) &&
			self::isSupportedImageFileExtension($ext) &&
			$this->hasSupportedExifImageType()
		) || (
			self::isSupportedVideoMimeType($mime) &&
			self::isSupportedVideoFileExtension($ext)
		);
	}

	/**
	 * Asserts that the file is supported.
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 */
	public function assertIsSupported(): void
	{
		if (!$this->isSupported()) {
			throw new MediaFileUnsupportedException();
		}
	}
}
