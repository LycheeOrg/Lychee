<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Image;

use App\Exceptions\MediaFileOperationException;

/**
 * Class BinaryBlob.
 *
 * A binary blob is a file-like object in the sense that one can write
 * to and read from it via streams.
 * However, it may also completely exist in memory and thus lack certain
 * properties of a file like access rights or a file name.
 *
 * In particular, this abstraction provides a unified copy-mechanism
 * between different Flysystem disks, local (native) files, uploaded files
 * and memory objects via
 *
 *     $targetBlob->write($sourceBlob->read())
 *
 * using streams.
 * This API is inspired by Flysystem.
 *
 * @property ?resource $stream
 */
interface BinaryBlob
{
	/**
	 * Returns a stream from which can be read.
	 *
	 * To free the stream after use, call {@link BinaryBlob::close()}.
	 * Calling `read` multiple times is safe.
	 * The read pointer of the stream will be reset to the beginning of
	 * the stream, without closing the stream in between.
	 *
	 * @return resource
	 *
	 * @throws MediaFileOperationException
	 */
	public function read();

	/**
	 * Writes the content of the provided stream into the blob.
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
	 * Closes the internal stream/buffer.
	 *
	 * The associated buffer is implicitly freed when this object becomes
	 * unreachable and is garbage-collected.
	 * Calling this function frees the memory explicitly.
	 * Note, the content of the freed buffer is lost (unless saved somewhere
	 * otherwise).
	 * It is safe to call {@link BinaryBlob::read()} and
	 * {@link BinaryBlob::write()} again after this method.
	 * A new buffer will be created, if needed.
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	public function close(): void;
}
