<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const TIMELINE = 'Mod Timeline';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'timeline_lens_height',
				'value' => '230',
				'cat' => self::TIMELINE,
				'type_range' => 'int:100:400',
				'description' => 'Timeline magnifying lens height (px)',
				'details' => 'Range 100-400. How tall the fisheye magnifying lens is when hovering the timeline date rail.',
				'is_secret' => false,
				'level' => 1,
				'is_expert' => true,
			],
			[
				'key' => 'timeline_lens_falloff',
				'value' => '17',
				'cat' => self::TIMELINE,
				'type_range' => 'int:5:50',
				'description' => 'Timeline magnifying lens falloff',
				'details' => 'Range 5-50 (0.5-5.0). Steepness of the fisheye magnification curve. Higher values give a sharper transition between magnified and normal-size dates; lower values give a smoother one. Actual value used is (value / 10).',
				'is_secret' => false,
				'level' => 1,
				'is_expert' => true,
			],
			[
				'key' => 'timeline_lens_magnification',
				'value' => '55',
				'cat' => self::TIMELINE,
				'type_range' => 'int:20:150',
				'description' => 'Timeline magnifying lens zoom level',
				'details' => 'Range 20-150 (2.0-15.0). How much dates are enlarged at the center of the fisheye lens. Actual multiplier used is (value / 10).',
				'is_secret' => false,
				'level' => 1,
				'is_expert' => true,
			],
		];
	}
};
