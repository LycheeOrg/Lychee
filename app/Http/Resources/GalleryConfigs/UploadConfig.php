<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use App\Facades\Helpers;
use App\Models\Configs;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class UploadConfig extends Data
{
	public int $upload_processing_limit;
	public int $upload_chunk_size;

	public function __construct()
	{
		$this->upload_processing_limit = max(1, Configs::getValueAsInt('upload_processing_limit'));
		$this->upload_chunk_size = self::getUploadLimit();
	}

	public static function getUploadLimit(): int
	{
		$size = Configs::getValueAsInt('upload_chunk_size');
		if ($size === 0) {
			try {
				$size = (int) min(
					Helpers::convertSize(ini_get('upload_max_filesize')),
					Helpers::convertSize(ini_get('post_max_size')),
					Helpers::convertSize(ini_get('memory_limit')) / 10
				);
			} catch (InfoException $e) {
				return 1024 * 1024;
			}
		}

		return $size;
	}
}
