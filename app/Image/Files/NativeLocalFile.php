<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Files;

use App\Exceptions\MediaFileOperationException;
use App\Image\StreamStat;
use App\Services\Image\FileExtensionService;
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
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
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
	 * @param string|null $mime_type the mime type of `$stream`
	 */
	public function write($stream, bool $collect_statistics = false, ?string $mime_type = null): ?StreamStat
	{
		try {
			$stream_stat = $collect_statistics ? static::appendStatFilter($stream) : null;

			if (is_resource($this->stream)) {
				ftruncate($this->stream, 0);
				rewind($this->stream);
			} else {
				$this->stream = fopen($this->getPath(), 'w+b');
			}
			$this->cachedMimeType = null;
			stream_copy_to_stream($stream, $this->stream);
			$this->cachedMimeType = $mime_type;
			// File statistics info (filesize, access mode, etc.) are cached
			// by PHP to avoid costly I/O calls.
			// If cache is not cleared, an old size may be reported after
			// write.
			clearstatcache(true, $this->getPath());

			return $stream_stat;
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * If new content is written to the file, the internally cached mime
	 * type is cleared.
	 * The mime type will be re-determined again upon the next invocation of
	 * {@link NativeLocalFile::getMimeType()}.
	 * This can be avoided by passing the MIME type of the stream.
	 *
	 * @param resource    $stream    the input stream which provides the input to write
	 * @param string|null $mime_type the mime type of `$stream`
	 */
	public function append($stream, bool $collect_statistics = false, ?string $mime_type = null): ?StreamStat
	{
		try {
			$stream_stat = $collect_statistics ? static::appendStatFilter($stream) : null;

			if (!is_resource($this->stream)) {
				$this->stream = fopen($this->getPath(), 'a+b');
			}
			$this->cachedMimeType = null;
			stream_copy_to_stream($stream, $this->stream);
			$this->cachedMimeType = $mime_type;
			// File statistics info (filesize, access mode, etc.) are cached
			// by PHP to avoid costly I/O calls.
			// If cache is not cleared, an old size may be reported after
			// write.
			clearstatcache(true, $this->getPath());

			return $stream_stat;
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
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
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * {@inheritDoc}
	 */
	public function move(string $new_path): void
	{
		try {
			rename($this->path, $new_path);
			$this->path = $new_path;
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
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
			// @codeCoverageIgnoreStart
		} catch (\ErrorException) {
			return false;
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastModified(): int
	{
		try {
			return filemtime($this->getPath());
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFilesize(): int
	{
		try {
			return filesize($this->getPath());
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns the path of the file.
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
	 * @throws MediaFileOperationException
	 */
	public function getRealPath(): string
	{
		try {
			return realpath($this->path);
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
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
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
	}

	// 	/**
	// 	 * Checks if the file is a valid image type acc. to {@link MediaFile::SUPPORTED_PHP_EXIF_IMAGE_TYPES}.
	// 	 *
	// 	 * @return bool true, if the file has a valid EXIF type
	// 	 */
	// 	protected function hasSupportedExifImageType(

	// 	): bool
	// 	{
	// 		try {
	// 			return in_array(exif_imagetype($this->getPath()), FileExtensionService::SUPPORTED_PHP_EXIF_IMAGE_TYPES, true);
	// 			// @codeCoverageIgnoreStart
	// 		} catch (\ErrorException|MediaFileOperationException) {
	// 			// `exif_imagetype` emit an engine error E_NOTICE, if it is unable
	// 			// to read enough bytes from the file to determine the image type.
	// 			// This may happen for short "raw" files.
	// 			return false;
	// 		}
	// 		// @codeCoverageIgnoreEnd
	// 	}

	// 	/**
	// 	 * Checks if the file is a supported image.
	// 	 *
	// 	 * @throws MediaFileOperationException
	// 	 */
	// 	public function isSupportedImage(FileExtensionService $file_extension_service): bool
	// 	{
	// 		$mime = $this->getMimeType();
	// 		$ext = $this->getOriginalExtension();

	// 		return
	// 			$file_extension_service->isSupportedImageMimeType($mime) &&
	// 			$file_extension_service->isSupportedImageFileExtension($ext) &&
	// 			$this->hasSupportedExifImageType();
	// 	}

	// 	/**
	// 	 * Checks if the file is a supported video.
	// 	 *
	// 	 * @throws MediaFileOperationException
	// 	 */
	// 	public function isSupportedVideo(FileExtensionService $file_extension_service): bool
	// 	{
	// 		$mime = $this->getMimeType();
	// 		$ext = $this->getOriginalExtension();

	// 		return
	// 			$file_extension_service->isSupportedVideoMimeType($mime) &&
	// 			$file_extension_service->isSupportedVideoFileExtension($ext);
	// 	}
}