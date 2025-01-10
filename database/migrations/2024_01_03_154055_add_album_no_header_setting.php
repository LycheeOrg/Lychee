<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const GALLERY = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'use_album_compact_header',
				'value' => '0',
				'confidentiality' => '0',
				'cat' => self::GALLERY,
				'type_range' => self::BOOL,
				'description' => 'Disable the header image in albums (0|1)',
			],
		];
	}
};
