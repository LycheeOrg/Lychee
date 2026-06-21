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
				'key' => 'ai_vision_face_recognition_warning',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Show face recognition legal warning',
				'details' => 'When enabled, a legal warning about facial recognition is displayed on the Face Clusters and Face Maintenance pages. An administrator can dismiss the warning.',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 0,
				'order' => 19,
			],
		];
	}
};
