<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photo_ken_burns_on_hover_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Enable a Ken Burns effect on photo hover',
				'details' => 'When enabled, hovering a photo thumbnail triggers a very slow zoom into the image, clipped inside the thumbnail so it never overlaps its neighbours.',
				'level' => 0,
				'order' => 103,
				'is_expert' => false,
			],
		];
	}
};
