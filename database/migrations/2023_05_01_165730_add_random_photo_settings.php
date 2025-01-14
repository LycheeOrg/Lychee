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
				'key' => 'random_album_id',
				'value' => 'starred',
				'cat' => 'Mod Frame',
				'type_range' => 'string',
				'confidentiality' => '0',
				'description' => 'Album id to be used by for random function.',
			],
		];
	}
};
