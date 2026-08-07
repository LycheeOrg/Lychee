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
				'key' => 'photo_highlight_on_hover',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Highlight the hovered photo and dim the others',
				'details' => 'When enabled, hovering a photo thumbnail slightly brightens it while dimming the other thumbnails on the page.',
				'level' => 0,
				'order' => 38,
				'is_expert' => false,
			],
			[
				'key' => 'photo_zoom_on_hover',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Zoom in on a photo thumbnail on hover',
				'details' => 'When enabled, hovering a photo thumbnail slightly scales it up.',
				'level' => 0,
				'order' => 39,
				'is_expert' => false,
			],
		];
	}
};
