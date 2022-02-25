<?php

namespace App\Image;

use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Models\Configs;

class ImageHandler implements ImageHandlerInterface
{
	public const NO_HANDLER_EXCEPTION_MSG = 'No suitable image handler found';

	private int $compressionQuality;
	/** @var ImageHandlerInterface[] */
	private array $engines;

	/**
	 * @param int $compressionQuality
	 */
	public function __construct(int $compressionQuality)
	{
		$this->compressionQuality = $compressionQuality;
		$this->engines = [];
		if (Configs::hasImagick()) {
			$this->engines[] = new ImagickHandler($this->compressionQuality);
		}
		$this->engines[] = new GdHandler($this->compressionQuality);
	}

	/**
	 * {@inheritDoc}
	 */
	public function scale(string $source, string $destination, int $newWidth, int $newHeight, int &$resWidth, int &$resHeight): void
	{
		$lastException = new MediaFileOperationException(self::NO_HANDLER_EXCEPTION_MSG);
		foreach ($this->engines as $engine) {
			try {
				$engine->scale($source, $destination, $newWidth, $newHeight, $resWidth, $resHeight);
				$lastException = null;
				break;
			} catch (MediaFileOperationException|MediaFileUnsupportedException $e) {
				$lastException = $e;
			}
		}
		if ($lastException) {
			throw $lastException;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function crop(string $source, string $destination, int $newWidth, int $newHeight): void
	{
		$lastException = new MediaFileOperationException(self::NO_HANDLER_EXCEPTION_MSG);
		foreach ($this->engines as $engine) {
			try {
				$engine->crop($source, $destination, $newWidth, $newHeight);
				$lastException = null;
				break;
			} catch (MediaFileOperationException|MediaFileUnsupportedException $e) {
				$lastException = $e;
			}
		}
		if ($lastException) {
			throw $lastException;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function autoRotate(string $path, int $orientation = 1, bool $pretend = false): array
	{
		$lastException = new MediaFileOperationException(self::NO_HANDLER_EXCEPTION_MSG);
		$ret = [];
		foreach ($this->engines as $engine) {
			try {
				$ret = $engine->autoRotate($path, $orientation, $pretend);
				$lastException = null;
				break;
			} catch (MediaFileOperationException|MediaFileUnsupportedException $e) {
				$lastException = $e;
			}
		}
		if ($lastException) {
			throw $lastException;
		}

		return $ret;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rotate(string $source, int $angle, string $destination = null): void
	{
		if ($angle != 90 && $angle != -90) {
			throw new LycheeDomainException('Angle value out-of-bounds');
		}

		$lastException = new MediaFileOperationException(self::NO_HANDLER_EXCEPTION_MSG);
		foreach ($this->engines as $engine) {
			try {
				$engine->rotate($source, $angle, $destination);
				$lastException = null;
				break;
			} catch (MediaFileOperationException|MediaFileUnsupportedException $e) {
				$lastException = $e;
			}
		}
		if ($lastException) {
			throw $lastException;
		}
	}
}
