<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'size_variant_format',
				'value' => 'original',
				'cat' => self::PROCESSING,
				'type_range' => 'original|jpeg|webp',
				'description' => 'Format of generated size variants',
				'details' => 'Original: thumbs are JPEG, small and medium keep the format of the uploaded file. JPEG or WebP: all generated size variants use that format. Only applies to newly generated size variants.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 10,
			],
		];
	}
};
