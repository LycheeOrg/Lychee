<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'AI Vision';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'ai_vision_face_overlay_enabled',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable face overlay on photos',
				'details' => 'Master toggle for face bounding-box overlays. When disabled (0), no face overlays or face circles are shown anywhere in the UI, regardless of detection results.',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 1,
				'order' => 17,
			],
			[
				'key' => 'ai_vision_face_overlay_default_visibility',
				'value' => 'visible',
				'cat' => self::CAT,
				'type_range' => 'visible|hidden',
				'description' => 'Default visibility of face overlay when viewing a photo',
				'details' => 'Sets whether face overlays are shown (visible) or hidden by default when a photo is opened. Users can toggle visibility with the P key.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 18,
			],
		];
	}
};
