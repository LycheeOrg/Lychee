<?php

namespace App\Image;

use App\Exceptions\MediaFileOperationException;

class InMemoryBuffer extends BinaryBlob
{
	/**
	 * The maximum size of the buffer in bytes which is kept in memory.
	 *
	 * If the maximum is hit, the buffer is swapped to disk.
	 * 50 MB should be sufficient for most image files except RAW.
	 */
	public const MAX_SIZE = 50 * 1024 * 1024;

	/**
	 * Returns a stream from which can be read.
	 *
	 * Calling `read` multiple times is safe.
	 * The read pointer of the stream will be reset to the beginning of
	 * the stream, without closing the stream in between.
	 *
	 * @return resource a readable stream
	 *
	 * @throws MediaFileOperationException
	 */
	public function read()
	{
		try {
			$this->stream();
			\Safe\rewind($this->stream);

			return $this->stream;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * Writes the content of the provided stream into the buffer.
	 *
	 * Any previous content is overwritten.
	 * The freshly written content can immediately be read back via
	 * {@link MediaFile::read}.
	 *
	 * @param resource $stream            the input stream to copy from
	 * @param bool     $collectStatistics if true, the method returns statistics about the stream
	 *
	 * @return ?StreamStat optional statistics about the stream, if requested
	 *
	 * @throws MediaFileOperationException
	 */
	public function write($stream, bool $collectStatistics = false): ?StreamStat
	{
		try {
			$streamStat = $collectStatistics ? static::appendStatFilter($stream) : null;

			$this->stream();
			\Safe\ftruncate($this->stream, 0);
			\Safe\rewind($this->stream);
			\Safe\stream_copy_to_stream($stream, $this->stream);

			return $streamStat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * Returns a stream from which can be read/written/seeked.
	 *
	 * Calling `stream` multiple times is safe.
	 * As long a stream is opened, it will always return the same
	 * stream and not modify the position of the read/write pointer.
	 * If no stream is opened, a new buffer will be created.
	 *
	 * @return resource a readable stream
	 *
	 * @throws MediaFileOperationException
	 */
	public function stream()
	{
		try {
			if (!is_resource($this->stream)) {
				$this->stream = \Safe\fopen('php://temp/maxmemory:' . self::MAX_SIZE, 'r+b');
			}

			return $this->stream;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}
}
