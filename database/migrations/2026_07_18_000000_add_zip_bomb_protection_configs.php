<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Enum\ConfigType;
use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const PROCESSING = 'Image Processing';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'zip_bomb_max_total_size',
				'value' => '10GB',
				'cat' => self::PROCESSING,
				'type_range' => ConfigType::FILE_SIZE->value,
				'description' => 'Maximum combined uncompressed size of an uploaded zip archive.',
				'details' => 'Format: a number followed by B, KB, MB, GB or TB (e.g. "10GB"). Protects against zip-bomb attacks. The archive is rejected if its declared or actual uncompressed size exceeds this limit.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 88,
			],
			[
				'key' => 'zip_bomb_max_file_size',
				'value' => '5GB',
				'cat' => self::PROCESSING,
				'type_range' => ConfigType::FILE_SIZE->value,
				'description' => 'Maximum uncompressed size of any single file within an uploaded zip archive.',
				'details' => 'Format: a number followed by B, KB, MB, GB or TB (e.g. "5GB"). Protects against zip-bomb attacks. The archive is rejected if any single entry exceeds this limit when uncompressed.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 89,
			],
			[
				'key' => 'zip_bomb_max_entries',
				'value' => '10000',
				'cat' => self::PROCESSING,
				'type_range' => ConfigType::POSTIIVE->value,
				'description' => 'Maximum number of entries allowed in an uploaded zip archive.',
				'details' => 'Protects against "many tiny files" denial-of-service attacks. The archive is rejected if it contains more entries than this.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 90,
			],
			[
				'key' => 'zip_bomb_max_ratio',
				'value' => '200',
				'cat' => self::PROCESSING,
				'type_range' => ConfigType::POSTIIVE->value,
				'description' => 'Maximum allowed compression ratio (uncompressed / compressed) of an uploaded zip archive.',
				'details' => 'Protects against zip-bomb attacks that rely on extreme compression ratios. The archive is rejected if its overall ratio exceeds this value.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 91,
			],
		];
	}
};
