<?php

namespace App\Image;

use App\Exceptions\MediaFileOperationException;
use App\Models\Configs;
use App\Models\Logs;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

abstract class BaseImageHandler implements ImageHandlerInterface
{
	public const DEFAULT_COMPRESSION_QUALITY = 75;

	/** @var int the desired compression quality, only used for JPEG during save */
	protected int $compressionQuality = self::DEFAULT_COMPRESSION_QUALITY;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(int $compressionQuality = self::DEFAULT_COMPRESSION_QUALITY)
	{
		$this->compressionQuality = $compressionQuality;
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
	 */
	protected static function applyLosslessOptimizationConditionally(MediaFile $file, bool $collectStatistics = false): ?StreamStat
	{
		if (Configs::get_value('lossless_optimization', '0') == '1') {
			if ($file instanceof NativeLocalFile) {
				ImageOptimizer::optimize($file->getAbsolutePath());

				return $collectStatistics ? StreamStat::createFromLocalFile($file) : null;
			} else {
				Logs::warning(__METHOD__, __LINE__, 'Skipping lossless optimization; optimization is requested by configuration but only supported for local files');

				return null;
			}
		} else {
			return null;
		}
	}
}
