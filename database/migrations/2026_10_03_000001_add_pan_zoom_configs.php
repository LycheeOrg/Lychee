<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'gestures';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'photo_click_action',
				'value' => 'overlay',
				'cat' => self::CAT,
				'type_range' => 'overlay|zoom',
				'is_secret' => false,
				'description' => 'Action of a click on the photo',
				'details' => '<i>overlay</i>: a click rotates the overlay. <i>zoom</i>: a click on the picture zooms to 2× and back, a click on the overlay rotates it.',
				'level' => 0,
				'order' => 3,
				'is_expert' => false,
			],
			[
				'key' => 'is_photo_minimap_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show the minimap when zoomed (desktop)',
				'details' => 'Devices without touch screen.',
				'level' => 0,
				'order' => 4,
				'is_expert' => false,
			],
			[
				'key' => 'is_photo_minimap_enabled_mobile',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'is_secret' => false,
				'description' => 'Show the minimap when zoomed (touch devices)',
				'details' => 'Devices with a touch screen.',
				'level' => 0,
				'order' => 5,
				'is_expert' => false,
			],
			[
				'key' => 'photo_minimap_idle_opacity',
				'value' => '25',
				'cat' => self::CAT,
				'type_range' => self::PERCENT_RANGE,
				'is_secret' => false,
				'description' => 'Minimap opacity when idle (desktop, %)',
				'details' => 'Range 0-100. Opacity of the minimap after the fade delay without interaction.',
				'level' => 0,
				'order' => 6,
				'is_expert' => false,
			],
			[
				'key' => 'photo_minimap_idle_opacity_mobile',
				'value' => '25',
				'cat' => self::CAT,
				'type_range' => self::PERCENT_RANGE,
				'is_secret' => false,
				'description' => 'Minimap opacity when idle (touch devices, %)',
				'details' => 'Range 0-100. Opacity of the minimap after the fade delay without interaction.',
				'level' => 0,
				'order' => 7,
				'is_expert' => false,
			],
			[
				'key' => 'photo_minimap_fade_delay',
				'value' => '2',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'is_secret' => false,
				'description' => 'Minimap fade delay (seconds)',
				'details' => 'Seconds without interaction before the minimap fades to its idle opacity.',
				'level' => 0,
				'order' => 8,
				'is_expert' => false,
			],
		];
	}
};
