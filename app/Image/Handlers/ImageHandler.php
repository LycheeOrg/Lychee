<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Image\Handlers;

use App\Contracts\Image\ImageHandlerInterface;
use App\Contracts\Image\MediaFile;
use App\Contracts\Image\StreamStats;
use App\DTO\ImageDimension;
use App\Exceptions\Handler;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use App\Models\Configs;

class ImageHandler extends BaseImageHandler implements ImageHandlerInterface
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
	public function __construct()
	{
		parent::__construct();
		if (Configs::hasImagick()) {
			$this->engineClasses[] = ImagickHandler::class;
		}
		$this->engineClasses[] = GdHandler::class;
	}

	public function __clone()
	{
		if ($this->engine !== null) {
			$this->engine = clone $this->engine;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(MediaFile $file): void
	{
		$this->reset();
		$lastException = null;

		foreach ($this->engineClasses as $engineClass) {
			try {
				$engine = new $engineClass();
				if ($engine instanceof ImageHandlerInterface) {
					$this->engine = $engine;
					$this->engine->load($file);

					return;
				} else {
					throw new LycheeLogicException('$engine is not an instance of ImageHandlerInterface');
				}
			} catch (\Throwable $e) {
				// Report the error to the log, but don't fail yet.
				Handler::reportSafely($e);
				$lastException = $e;
				$this->engine = null;
			}
		}

		throw new MediaFileOperationException(self::NO_HANDLER_EXCEPTION_MSG, $lastException);
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(MediaFile $file, bool $collectStatistics = false): ?StreamStats
	{
		return $this->engine->save($file, $collectStatistics);
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
	public function cloneAndScale(ImageDimension $dstDim): ImageHandlerInterface
	{
		return $this->engine->cloneAndScale($dstDim);
	}

	/**
	 * {@inheritDoc}
	 */
	public function cloneAndCrop(ImageDimension $dstDim): ImageHandlerInterface
	{
		return $this->engine->cloneAndCrop($dstDim);
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
		return $this->engine !== null && $this->engine->isLoaded();
	}
}
