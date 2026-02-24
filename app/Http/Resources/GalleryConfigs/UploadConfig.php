<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Facades\Helpers;
use App\Image\Watermarker;
use App\Repositories\ConfigManager;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UploadConfig extends Data
{
	public int $upload_processing_limit;
	public int $upload_chunk_size;
	public bool $can_watermark_optout;

	public function __construct()
	{
		$config_manager = resolve(ConfigManager::class);
		$this->upload_processing_limit = max(1, $config_manager->getValueAsInt('upload_processing_limit'));
		$this->upload_chunk_size = self::getUploadLimit();

		// Compute watermarker status
		$watermarker = resolve(Watermarker::class);
		$this->can_watermark_optout = $watermarker->can_watermark() && !$config_manager->getValueAsBool('watermark_optout_disabled');
	}

	public static function getUploadLimit(): int
	{
		$config_manager = resolve(ConfigManager::class);
		$size = $config_manager->getValueAsInt('upload_chunk_size');
		if ($size === 0) {
			try {
				$memory_size = Helpers::convertSize(ini_get('memory_limit')) / 10;
				if ($memory_size < 0) {
					$memory_size = INF;
				}
				$size = (int) min(
					Helpers::convertSize(ini_get('upload_max_filesize')),
					Helpers::convertSize(ini_get('post_max_size')),
					$memory_size
				);
			} catch (InfoException $e) {
				return 1024 * 1024;
			}
		}

		return $size;
	}
}
