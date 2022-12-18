<?php

namespace App\Image;

use App\Contracts\Image\StreamStats;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\NativeLocalFile;

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
			error_clear_last();
			$checksum = hash_file(StreamStatFilter::HASH_ALGO_NAME, $file->getPath());
			if ($checksum === false) {
				$error = error_get_last();
				throw new \ErrorException($error['message'] ?? 'An error occurred', 0, $error['type'] ?? 1);
			}

			return new StreamStat($file->getFilesize(), $checksum);
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}
}
