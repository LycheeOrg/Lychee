<?php

namespace App\Image;

use App\Exceptions\MediaFileOperationException;

abstract class BaseImageHandler implements ImageHandlerInterface
{
	/** @var int the desired compression quality, only used for JPEG during save */
	protected int $compressionQuality = 75;

	/** @var ?resource a readable/writable/seekable in-memory stream which holds an encoding of the image (e.g. a JPEG/TIFF/PNG/WEBP representation) */
	protected $bufferStream = null;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(int $compressionQuality)
	{
		$this->compressionQuality = $compressionQuality;
	}

	public function __destruct()
	{
		$this->reset();
	}

	public function __clone()
	{
		// We must not be the owner of an open buffer,
		// because the cloned object owns the buffer
		$this->bufferStream = null;
	}

	/**
	 * Creates a new in-memory stream for buffering.
	 *
	 * If a stream is given, the content of that stream is copied into the
	 * new buffer.
	 * The provided stream must be readable, seeking (rewinding) is not
	 * required.
	 *
	 * @param resource|null $stream a readable stream whose content is copied into the buffer
	 *
	 * @return void
	 *
	 * @throws MediaFileOperationException
	 */
	protected function createBuffer($stream = null): void
	{
		try {
			if (is_resource($this->bufferStream)) {
				throw new \RuntimeException('buffer already opened');
			}
			$this->bufferStream = fopen('php://memory', 'r+');
			if (!$this->bufferStream) {
				throw new \RuntimeException('fopen failed');
			}
			if (stream_copy_to_stream($stream, $this->bufferStream) === false) {
				throw new \RuntimeException('stream_copy_to_stream failed');
			}
			if (!rewind($this->bufferStream)) {
				throw new \RuntimeException('rewind failed');
			}
		} catch (\Throwable) {
			throw new MediaFileOperationException('Could not create buffer');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function close(): void
	{
		if (is_resource($this->bufferStream)) {
			fclose($this->bufferStream);
			$this->bufferStream = null;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->close();
	}
}
