<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'raw_download_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Allow users to download the original RAW file.',
				'details' => 'When enabled, users with download permissions can download the untouched RAW/HEIC/PSD file that was preserved during upload.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 17,
			],
		];
	}
};
