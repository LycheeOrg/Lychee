<?php

namespace App\Image;

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
 */
abstract class BinaryBlob
{
	/** @var ?resource */
	protected $stream = null;

	/**
	 * @throws MediaFileOperationException
	 */
	public function __destruct()
	{
		$this->close();
	}

	public function __clone()
	{
		// The stream belongs to the original object, it is not ours.
		$this->stream = null;
	}

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
	abstract public function read();

	/**
	 * Writes the content of the provided stream into the blob.
	 *
	 * @param resource $stream            the input stream which provides the input to write
	 * @param bool     $collectStatistics if true, the method returns statistics about the stream
	 *
	 * @return ?StreamStat optional statistics about the stream, if requested
	 *
	 * @throws MediaFileOperationException
	 */
	abstract public function write($stream, bool $collectStatistics = false): ?StreamStat;

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
	public function close(): void
	{
		try {
			if (is_resource($this->stream)) {
				\Safe\fclose($this->stream);
				$this->stream = null;
			}
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException($e->getMessage(), $e);
		}
	}

	/**
	 * Appends {@link StreamStatFilter} to the read-direction of the provided stream.
	 *
	 * @param resource $stream the stream whose statistic shall be collected
	 *
	 * @return StreamStat the stream statistics
	 */
	protected static function appendStatFilter($stream): StreamStat
	{
		$streamStat = new StreamStat();
		\Safe\stream_filter_append(
			$stream,
			StreamStatFilter::REGISTERED_NAME,
			STREAM_FILTER_READ,
			$streamStat
		);

		return $streamStat;
	}
}
