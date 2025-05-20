<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		// landing_background
		return [
			[
				'key' => 'photo_previous_next_size',
				'value' => 'small',
				'cat' => self::CAT,
				'type_range' => 'small|large',
				'description' => 'Select the size of the previous/next buttons in photo view.',
				'details' => 'Those buttons are hidden by default and only visible when the mouse get close to the left/right side of the screen.',
				'is_expert' => true,
				'is_secret' => true,
				'level' => 0,
				'order' => 36,
			],
		];
	}
};