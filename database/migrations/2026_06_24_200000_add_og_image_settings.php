<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CONFIG = 'config';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'sm_card_album_source',
				'value' => 'header',
				'cat' => self::CONFIG,
				'type_range' => 'header|cover',
				'description' => 'Album photo source for social media cards',
				'details' => 'Select whether the header or cover photo of an album is used as the Open Graph image when sharing links on social media.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 5,
			],
			[
				'key' => 'sm_card_image_url',
				'value' => '',
				'cat' => self::CONFIG,
				'type_range' => 'string',
				'description' => 'Fallback image URL or photo ID for social media cards',
				'details' => 'URL or photo ID used as the Open Graph image when no album-specific image is available. If empty, the landing page background is used.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 6,
			],
		];
	}
};
