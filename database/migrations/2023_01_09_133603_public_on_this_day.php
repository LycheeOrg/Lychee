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
				'key' => 'public_on_this_day',
				'value' => '0',
				'cat' => 'Smart Albums',
				'type_range' => self::BOOL,
				'confidentiality' => '0',
				'description' => 'Make "On This Day" smart album accessible to anonymous users',
			],
		];
	}
};
