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
				'key' => 'folder_upload_enabled',
				'value' => '1',
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Enable folder drag-and-drop album creation.',
				'details' => 'When enabled, dragging a folder onto the Albums page creates an album named after the folder and uploads its contents. Sub-folders become sub-albums recursively.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 30,
			],
			[
				'key' => 'folder_upload_max_depth',
				'value' => '5',
				'cat' => self::PROCESSING,
				'type_range' => self::INT,
				'description' => 'Maximum sub-folder recursion depth for folder uploads (0 = unlimited).',
				'details' => 'Controls how many sub-folder levels are processed when a folder is dropped. 1 means only the top-level dropped folder; 2 means one level of sub-folders; 0 means no limit.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 31,
			],
		];
	}
};
