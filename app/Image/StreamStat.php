<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Image;

use App\Contracts\Image\StreamStats;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\NativeLocalFile;
use function Safe\hash_file;

/**
 * Class `StreamStat` holds statistics about a read/written (image) stream.
 *
 * Traditionally, Lychee used `filesize()` and `sha1_file()` to get the
 * size and checksum of an image.
 * However, these methods require a file path which is not available with
 * generic streams.
 * This class provides these values which are collected while the stream
 * is "on the fly".
 */
class StreamStat implements StreamStats
{
	public int $bytes;
	public string $checksum;

	public function __construct(int $bytes = 0, string $checksum = '')
	{
		$this->bytes = $bytes;
		$this->checksum = $checksum;
	}

	/**
	 * Creates a new object from a native local file.
	 *
	 * Use this method rarely! The class is intended to be used with streams.
	 * In particular, the checksum of file (or binary blob) can be
	 * calculated on the fly while the content of the file is written
	 * via {@link BinaryBlob::write()}.
	 * Calculating the stream statistics on-the-fly avoids reading back the
	 * file from disk after it has been written.
	 * This method is merely meant for the rare cases where we don't have
	 * an in-memory copy of the file in the first place.
	 *
	 * @throws MediaFileOperationException
	 */
	public static function createFromLocalFile(NativeLocalFile $file): StreamStat
	{
		try {
			$checksum = hash_file(StreamStatFilter::HASH_ALGO_NAME, $file->getPath());

			return new StreamStat($file->getFilesize(), $checksum);
			// @codeCoverageIgnoreStart
		} catch (\Throwable $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
		// @codeCoverageIgnoreEnd
	}
}
