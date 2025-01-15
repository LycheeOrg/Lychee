<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_decoration',
				'value' => 'layers',
				'confidentiality' => '0',
				'cat' => 'Gallery',
				'type_range' => 'none|layers|album|photo|all',
				'description' => 'Show decorations on album cover (sub-album and/or photo count)',
			],
			[
				'key' => 'album_decoration_orientation',
				'value' => 'row',
				'confidentiality' => '0',
				'cat' => 'Gallery',
				'type_range' => 'column|column-reverse|row|row-reverse',
				'description' => 'Align album decorations horizontally or vertically',
			],
		];
	}
};
