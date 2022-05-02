<?php

namespace App\Image;

use App\DTO\ImageDimension;
use App\Exceptions\MediaFileOperationException;
use App\Models\Configs;

class ImageHandler extends BaseImageHandler
{
	public const NO_HANDLER_EXCEPTION_MSG = 'No suitable image handler found';

	/**
	 * The class names of the engines to use.
	 *
	 * @var string[]
	 */
	protected array $engineClasses = [];

	/**
	 * The selected image handler.
	 *
	 * @var ImageHandlerInterface|null
	 */
	protected ?ImageHandlerInterface $engine = null;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(int $compressionQuality = BaseImageHandler::DEFAULT_COMPRESSION_QUALITY)
	{
		parent::__construct($compressionQuality);
		if (Configs::hasImagick()) {
			$this->engineClasses[] = ImagickHandler::class;
		}
		$this->engineClasses[] = GdHandler::class;
	}

	public function __clone()
	{
		if ($this->engine) {
			$this->engine = clone $this->engine;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(MediaFile $file): void
	{
		$this->reset();

		foreach ($this->engineClasses as $engineClass) {
			try {
				$this->engine = new $engineClass($this->compressionQuality);
				$this->engine->load($file);

				return;
			} catch (\Throwable $e) {
				// Report the error to the log, but don't fail yet.
				report($e);
				$this->engine = null;
			}
		}

		throw new MediaFileOperationException(self::NO_HANDLER_EXCEPTION_MSG);
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(MediaFile $file): StreamStat
	{
		return $this->engine->save($file);
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		$this->engine?->reset();
		$this->engine = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function scale(ImageDimension $dstDim): ImageDimension
	{
		return $this->engine->scale($dstDim);
	}

	/**
	 * {@inheritDoc}
	 */
	public function crop(ImageDimension $dstDim): void
	{
		$this->engine->crop($dstDim);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rotate(int $angle): ImageDimension
	{
		return $this->engine->rotate($angle);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDimensions(): ImageDimension
	{
		return $this->engine->getDimensions();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isLoaded(): bool
	{
		return $this->engine && $this->engine->isLoaded();
	}
}
