<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use App\Legacy\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_NSFW = 'Mod NSFW';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'nsfw_banner_blur_backdrop',
				'value' => '0',
				'confidentiality' => '0',
				'cat' => self::MOD_NSFW,
				'type_range' => self::BOOL,
				'description' => 'Blur background instead of dark red opaque.',
			],
		];
	}
};
