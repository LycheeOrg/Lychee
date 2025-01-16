<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Image\StreamStat;
use function Safe\filemtime;
use function Safe\filesize;
use function Safe\fopen;
use function Safe\ftruncate;
use function Safe\mime_content_type;
use function Safe\realpath;
use function Safe\rename;
use function Safe\rewind;
use function Safe\stream_copy_to_stream;
use function Safe\unlink;

/**
 * Class NativeLocalFile.
 *
 * Represents a file which must be handled with native PHP methods
 * like `fopen`, etc.
 * This mostly applies to files which are uploaded to the server or
 * imported from the server and thus are located outside any Flysystem disk.
 */
class NativeLocalFile extends BaseMediaFile
{
	protected string $path;
	protected ?string $cachedMimeType;

	/**
	 * @param string $path the file path
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
		try {
			if (is_resource($this->stream)) {
				rewind($this->stream);
			} else {
				$this->stream = fopen($this->getPath(), 'rb');
			}

			return $this->stream;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
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
	 *
	 * @returns void
	 */
	public function write($stream, bool $collectStatistics = false, ?string $mimeType = null): ?StreamStat
	{
		try {
			$streamStat = $collectStatistics ? static::appendStatFilter($stream) : null;

			if (is_resource($this->stream)) {
				ftruncate($this->stream, 0);
				rewind($this->stream);
			} else {
				$this->stream = fopen($this->getPath(), 'w+b');
			}
			$this->cachedMimeType = null;
			stream_copy_to_stream($stream, $this->stream);
			$this->cachedMimeType = $mimeType;
			// File statistics info (filesize, access mode, etc.) are cached
			// by PHP to avoid costly I/O calls.
			// If cache is not cleared, an old size may be reported after
			// write.
			clearstatcache(true, $this->getPath());

			return $streamStat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * If new content is written to the file, the internally cached mime
	 * type is cleared.
	 * The mime type will be re-determined again upon the next invocation of
	 * {@link NativeLocalFile::getMimeType()}.
	 * This can be avoided by passing the MIME type of the stream.
	 *
	 * @param resource    $stream   the input stream which provides the input to write
	 * @param string|null $mimeType the mime type of `$stream`
	 *
	 * @returns void
	 */
	public function append($stream, bool $collectStatistics = false, ?string $mimeType = null): ?StreamStat
	{
		try {
			$streamStat = $collectStatistics ? static::appendStatFilter($stream) : null;

			if (!is_resource($this->stream)) {
				$this->stream = fopen($this->getPath(), 'a+b');
			}
			$this->cachedMimeType = null;
			stream_copy_to_stream($stream, $this->stream);
			$this->cachedMimeType = $mimeType;
			// File statistics info (filesize, access mode, etc.) are cached
			// by PHP to avoid costly I/O calls.
			// If cache is not cleared, an old size may be reported after
			// write.
			clearstatcache(true, $this->getPath());

			return $streamStat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(): void
	{
		try {
			// Close stream before deletion in case any stream is opened
			$this->close();
			// `is_file` returns false for links, so we must check separately with `is_link`
			if (is_link($this->path) || is_file($this->path)) {
				unlink($this->path);
			}
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function move(string $newPath): void
	{
		try {
			rename($this->path, $newPath);
			$this->path = $newPath;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
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
		try {
			return is_file(realpath($this->path));
		} catch (\ErrorException) {
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastModified(): int
	{
		try {
			return filemtime($this->getPath());
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFilesize(): int
	{
		try {
			return filesize($this->getPath());
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * Returns the path of the file.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Returns the real path of the file after all symbolic links and
	 * relative path components such as `'..'` have been resolved.
	 *
	 * Throws an exception, if the file does not exist.
	 *
	 * @return string
	 *
	 * @throws MediaFileOperationException
	 */
	public function getRealPath(): string
	{
		try {
			return realpath($this->path);
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExtension(): string
	{
		$ext = pathinfo($this->path, PATHINFO_EXTENSION);

		return $ext !== '' ? '.' . $ext : '';
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
		try {
			if ($this->cachedMimeType === null) {
				$this->cachedMimeType = mime_content_type($this->getPath());
			}

			return $this->cachedMimeType;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * Checks if the file is a valid image type acc. to {@link MediaFile::SUPPORTED_PHP_EXIF_IMAGE_TYPES}.
	 *
	 * @return bool true, if the file has a valid EXIF type
	 */
	protected function hasSupportedExifImageType(): bool
	{
		try {
			return in_array(exif_imagetype($this->getPath()), self::SUPPORTED_PHP_EXIF_IMAGE_TYPES, true);
		} catch (\ErrorException|MediaFileOperationException) {
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
