<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_WATERMARKER = 'Mod Watermarker';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'watermark_optout_disabled',
				'value' => '0',
				'cat' => self::MOD_WATERMARKER,
				'type_range' => self::BOOL,
				'description' => 'Disable watermark opt-out during upload',
				'details' => 'When enabled, users cannot opt-out of watermarking their uploads. All photos will be watermarked according to global settings.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 15,
			],
		];
	}
};
