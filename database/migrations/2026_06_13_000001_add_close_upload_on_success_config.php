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
				'key' => 'close_upload_on_success',
				'value' => '0',
				'cat' => self::PROCESSING,
				'type_range' => self::BOOL,
				'description' => 'Auto-close the upload panel when all uploads complete without errors.',
				'details' => 'When enabled, the upload panel will automatically close once all uploads finish successfully. If any upload fails or produces a warning, the panel remains open.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 28,
			],
		];
	}
};
