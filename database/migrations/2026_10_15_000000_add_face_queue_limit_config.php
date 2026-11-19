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
				'key' => 'ai_vision_face_queue_limit',
				'value' => '500',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Maximum faces per quick naming batch.',
				'details' => 'The quick naming view loads at most this many unnamed faces at once and fetches more when the end is reached.',
				'is_secret' => false,
				'is_expert' => true,
				'level' => 0,
				'order' => 19,
				'required_keys' => 'ai_vision_enabled,ai_vision_face_enabled',
			],
		];
	}
};
