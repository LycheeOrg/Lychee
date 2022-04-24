<?php

namespace App\Image;

use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;

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
	protected string $path;
	protected ?string $cachedMimeType;

	/**
	 * @param string $path the file path
	 *
	 * @throws MediaFileOperationException
	 */
	public function __construct(string $path)
	{
		$this->path = $path;
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
		try {
			$this->stream = fopen($this->getAbsolutePath(), 'rb');
			if ($this->stream === false || !is_resource($this->stream)) {
				$this->stream = null;
				throw new MediaFileOperationException('fopen failed');
			}
		} catch (\Throwable $e) {
			throw new MediaFileOperationException('Could not read from file ' . $this->path, $e);
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
		try {
			$this->cachedMimeType = null;
			// inspired from \League\Flysystem\Adapter\Local
			$this->stream = fopen($this->getAbsolutePath(), 'wb');
			if (
				!is_resource($this->stream) ||
				stream_copy_to_stream($stream, $this->stream) === false ||
				!fclose($this->stream)
			) {
				throw new MediaFileOperationException('fopen/stream_copy_to_stream/fclose failed');
			}
			$this->stream = null;
			$this->cachedMimeType = $mimeType;
		} catch (\Throwable $e) {
			throw new MediaFileOperationException('Could not write file ' . $this->path, $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(): void
	{
		// `is_file` returns false for links, so we must check separately with `is_link`
		if (is_link($this->path) || is_file($this->path)) {
			if (!unlink($this->path)) {
				throw new MediaFileOperationException('Could not delete file ' . $this->path);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * If the represented file is a symbolic link, then the method only
	 * returns true, if the link (as a file) exists and the target of the
	 * link exists, too.
	 */
	public function exists(): bool
	{
		$result = realpath($this->path);

		return ($result !== false) && is_file($result);
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastModified(): int
	{
		$result = filemtime($this->getAbsolutePath());
		if ($result === false) {
			throw new MediaFileOperationException('filemtime failed');
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFilesize(): int
	{
		$result = filesize($this->getAbsolutePath());
		if ($result === false) {
			throw new MediaFileOperationException('filesize failed');
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAbsolutePath(): string
	{
		$result = realpath($this->path);
		if ($result === false || !is_file($result)) {
			throw new MediaFileOperationException('The path "' . $result . '" does not point to a local file');
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExtension(): string
	{
		$ext = pathinfo($this->path, PATHINFO_EXTENSION);

		return $ext ? '.' . $ext : '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBasename(): string
	{
		return pathinfo($this->path, PATHINFO_FILENAME);
	}

	/**
	 * Returns the MIME type of the file.
	 *
	 * @return string the MIME type
	 *
	 * @throws MediaFileOperationException
	 */
	public function getMimeType(): string
	{
		if (!$this->cachedMimeType) {
			$this->cachedMimeType = mime_content_type($this->getAbsolutePath());
		}

		return $this->cachedMimeType;
	}

	/**
	 * Checks if the file is a valid image type acc. to {@link MediaFile::SUPPORTED_PHP_EXIF_IMAGE_TYPES}.
	 *
	 * @return bool true, if the file has a valid EXIF type
	 */
	protected function hasSupportedExifImageType(): bool
	{
		try {
			return in_array(exif_imagetype($this->getAbsolutePath()), self::SUPPORTED_PHP_EXIF_IMAGE_TYPES, true);
		} catch (\Throwable) {
			// `exif_imagetype` emit an engine error E_NOTICE, if it is unable
			// to read enough bytes from the file to determine the image type.
			// This may happen for short "raw" files.
			return false;
		}
	}

	/**
	 * Checks if the file is a supported image.
	 *
	 * @return bool
	 *
	 * @throws MediaFileOperationException
	 */
	public function isSupportedImage(): bool
	{
		$mime = $this->getMimeType();
		$ext = $this->getOriginalExtension();

		return
			self::isSupportedImageMimeType($mime) &&
			self::isSupportedImageFileExtension($ext) &&
			$this->hasSupportedExifImageType();
	}

	/**
	 * Checks if the file is a supported video.
	 *
	 * @return bool
	 *
	 * @throws MediaFileOperationException
	 */
	public function isSupportedVideo(): bool
	{
		$mime = $this->getMimeType();
		$ext = $this->getOriginalExtension();

		return
			self::isSupportedVideoMimeType($mime) &&
			self::isSupportedVideoFileExtension($ext);
	}

	/**
	 * Checks if the file is supported (image or video).
	 *
	 * @return bool true, if the file is supported
	 *
	 * @throws MediaFileOperationException
	 */
	public function isSupported(): bool
	{
		return
			$this->isSupportedImage() ||
			$this->isSupportedVideo();
	}

	/**
	 * Asserts that the file is supported.
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function assertIsSupported(): void
	{
		if (!$this->isSupported()) {
			throw new MediaFileUnsupportedException();
		}
	}

	/**
	 * Checks if the file is not supported, but an accepted raw media.
	 *
	 * @return bool
	 */
	public function isAcceptedRaw(): bool
	{
		return in_array(
			strtolower($this->getOriginalExtension()),
			self::getSanitizedAcceptedRawFileExtensions(),
			true
		);
	}

	/**
	 * Checks if the file is supported or accepted (i.e. image, video or raw).
	 *
	 * @return bool true, if the file is supported or accepted
	 *
	 * @throws MediaFileOperationException
	 */
	public function isSupportedMediaOrAcceptedRaw(): bool
	{
		return $this->isSupported() || $this->isAcceptedRaw();
	}

	/**
	 * Asserts that the file is supported or accepted (i.e. image, video or raw).
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 * @throws MediaFileOperationException
	 */
	public function assertIsSupportedMediaOrAcceptedRaw(): void
	{
		if (!$this->isSupportedMediaOrAcceptedRaw()) {
			throw new MediaFileUnsupportedException();
		}
	}
}
