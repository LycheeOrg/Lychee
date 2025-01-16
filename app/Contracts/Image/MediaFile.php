<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Image;

use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;

/**
 * Class `MediaFile` provides the common interface of all file-like classes.
 *
 * This class abstracts from the differences of files which are provided
 * through a Flysystem adapter and files outside Flysystem.
 * In particular, this abstraction provides a unified copy-mechanism
 * between different {@link BinaryBlob}using streams.
 *
 * This stream-based approach is the same which is also used by
 * {@link \Illuminate\Http\UploadedFile::storeAs()} under the hood and avoids certain problems
 * which are may be caused by PHP method like `rename`, `move` or `copy`.
 * Firstly, these methods need a file path and thus do not work, if a file
 * resides on a Flysystem disk for which PHP has no native handler (e.g.
 * AWS S3 storage).
 * Secondly, `rename` struggles with filesystem permissions and ownership, if
 * the file is moved within the same path namespace but across mount points.
 * Copying via streams avoids issues like
 * [LycheeOrg/Lychee#1198](https://github.com/LycheeOrg/Lychee/issues/1198).
 */
interface MediaFile extends BinaryBlob
{
	/**
	 * Writes the content of the provided stream into the file.
	 *
	 * Any previous content of the file is overwritten.
	 * The new content is buffered in memory and may not be synced to disk
	 * until {@link MediaFile::close()} is called.
	 * If you want to be sure, that the content is really written to disk,
	 * explicitly call {@link MediaFile::close()}.
	 * The freshly written content can immediately be read back via
	 * {@link MediaFile::read} without closing the file in between.
	 *
	 * @param resource $stream            the input stream which provides the input to write
	 * @param bool     $collectStatistics if true, the method returns statistics about the stream
	 *
	 * @return ?StreamStats optional statistics about the stream, if requested
	 *
	 * @throws MediaFileOperationException
	 */
	public function write($stream, bool $collectStatistics = false): ?StreamStats;

	/**
	 * Deletes the file.
	 *
	 * In case the file does not exist, the method is a silent no-op.
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	public function delete(): void;

	/**
	 * Moves the file to the new location efficiently.
	 *
	 * Basically the file is renamed; however, this kind of "renaming" also
	 * may change the path of the file.
	 * Note, that the path is interpreted relative to the "mount" point of
	 * the underlying filesystem implementation.
	 *
	 * @param string $newPath
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	public function move(string $newPath): void;

	/** Checks if the file exists.
	 *
	 * @return bool true, if the file exists
	 */
	public function exists(): bool;

	/**
	 * Returns the time of last modification as UNIX timestamp.
	 *
	 * @return int the time of last modification since epoch
	 *
	 * @throws MediaFileOperationException
	 */
	public function lastModified(): int;

	/**
	 * Returns the size of the file in bytes.
	 *
	 * @return int the file size in bytes
	 *
	 * @throws MediaFileOperationException
	 */
	public function getFilesize(): int;

	/**
	 * Returns the extension of the file incl. a preceding dot.
	 *
	 * @return string
	 */
	public function getExtension(): string;

	/**
	 * Returns the original extension of the file incl. the preceding dot.
	 *
	 * Normally, the original extension equals the extension.
	 * However, for temporary copies of downloaded or uploaded files the
	 * original extension is the extension as used by the source while the
	 * actual, physical extension is typically random.
	 *
	 * @return string
	 */
	public function getOriginalExtension(): string;

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
	public function getBasename(): string;

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
	public function getOriginalBasename(): string;

	/**
	 * Checks if the given MIME type designates a supported image type.
	 *
	 * @param string $mimeType the MIME type
	 *
	 * @return bool
	 */
	public static function isSupportedImageMimeType(string $mimeType): bool;

	/**
	 * Checks if the given MIME type designates a supported video type.
	 *
	 * @param string $mimeType the MIME type
	 *
	 * @return bool
	 */
	public static function isSupportedVideoMimeType(string $mimeType): bool;

	/**
	 * Checks if the given file extension is a supported image extension.
	 *
	 * @param string $extension the file extension
	 *
	 * @return bool
	 */
	public static function isSupportedImageFileExtension(string $extension): bool;

	/**
	 * Checks if the given file extension is a supported image extension.
	 *
	 * @param string $extension the file extension
	 *
	 * @return bool
	 */
	public static function isSupportedVideoFileExtension(string $extension): bool;

	/**
	 * Checks if the given file extension is supported.
	 *
	 * @param string $extension the file extension
	 *
	 * @return bool
	 */
	public static function isSupportedFileExtension(string $extension): bool;

	/**
	 * Checks if the given extension is accepted as raw.
	 *
	 * @param string $extension the file extension
	 *
	 * @return bool
	 */
	public static function isAcceptedRawFileExtension(string $extension): bool;

	/**
	 * Check if the given extension is supported or accepted.
	 *
	 * @param string $extension the file extension
	 *
	 * @return bool
	 */
	public static function isSupportedOrAcceptedFileExtension(string $extension): bool;

	/**
	 * Asserts that the given extension is supported or accepted.
	 *
	 * @param string $extension the file extension
	 *
	 * @return void
	 *
	 * @throws MediaFileUnsupportedException
	 */
	public static function assertIsSupportedOrAcceptedFileExtension(string $extension): void;
}
