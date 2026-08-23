<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Gallery';
	public const SCALE_RANGE = 'int:0:100';
	public const DURATION_RANGE = 'int:1:60';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photo_ken_burns_on_hover_scale',
				'value' => '50',
				'cat' => self::CAT,
				'type_range' => self::SCALE_RANGE,
				'is_secret' => false,
				'description' => 'Ken Burns hover zoom amount (%)',
				'details' => 'Range 0-100. Controls how far the image zooms in while hovering: the scale factor is (value / 100 + 1).',
				'level' => 0,
				'order' => 104,
				'is_expert' => true,
			],
			[
				'key' => 'photo_ken_burns_on_hover_duration',
				'value' => '15',
				'cat' => self::CAT,
				'type_range' => self::DURATION_RANGE,
				'is_secret' => false,
				'description' => 'Ken Burns hover zoom duration (seconds)',
				'details' => 'How long the slow zoom takes to reach its full scale while hovering a photo.',
				'level' => 0,
				'order' => 105,
				'is_expert' => true,
			],
		];
	}
};
