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
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\FlysystemFile;
use App\Image\Files\NativeLocalFile;
use App\Image\StreamStat;
use App\Models\Configs;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

abstract class BaseImageHandler implements ImageHandlerInterface
{
	/** @var int the desired compression quality, only used for JPEG during save */
	protected int $compressionQuality;

	/**
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct()
	{
		$this->compressionQuality = Configs::getValueAsInt('compression_quality');
	}

	public function __destruct()
	{
		$this->reset();
	}

	/**
	 * Optimizes a local image, if enabled.
	 *
	 * If lossless optimization is enabled via configuration, this method
	 * tries to apply the optimization to the provided file.
	 * If the file is not a local file, optimization is skipped and a warning
	 * is logged.
	 *
	 * TODO: Do we really need it? It does neither seem lossless nor doing anything useful.
	 *
	 * @param MediaFile $file
	 * @param bool      $collectStatistics if true, the method returns statistics about the stream
	 *
	 * @return StreamStat|null optional statistics about the stream, if optimization took place and if requested
	 *
	 * @throws MediaFileOperationException
	 * @throws ConfigurationKeyMissingException
	 */
	protected static function applyLosslessOptimizationConditionally(MediaFile $file, bool $collectStatistics = false): ?StreamStats
	{
		if (Configs::getValueAsBool('lossless_optimization')) {
			if ($file instanceof NativeLocalFile) {
				ImageOptimizer::optimize($file->getRealPath());

				return $collectStatistics ? StreamStat::createFromLocalFile($file) : null;
			} elseif ($file instanceof FlysystemFile && $file->isLocalFile()) {
				$localFile = $file->toLocalFile();
				ImageOptimizer::optimize($localFile->getRealPath());

				return $collectStatistics ? StreamStat::createFromLocalFile($localFile) : null;
			} else {
				Log::warning(__METHOD__ . ':' . __LINE__ . ' Skipping lossless optimization; optimization is requested by configuration but only supported for local files');

				return null;
			}
		} else {
			return null;
		}
	}
}
