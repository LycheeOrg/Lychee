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
				'key' => 'zip_bomb_delete_rejected_file',
				'value' => '1',
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Delete the uploaded zip file when it is rejected as a zip bomb.',
				'details' => 'When enabled, the original zip file is deleted as soon as it is detected as exceeding the configured zip-bomb protection limits. Disable to keep the file on disk for manual inspection.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 92,
			],
		];
	}
};
