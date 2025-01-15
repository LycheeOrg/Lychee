<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const IMAGE_PROCESSING = 'Image Processing';
	public const INT = 'int';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'upload_chunk_size',
				'value' => '0',
				'confidentiality' => '0',
				'cat' => self::IMAGE_PROCESSING,
				'type_range' => self::INT,
				'description' => 'Size of chunks when uploading in bytes: 0 is auto',
			],
		];
	}
};
