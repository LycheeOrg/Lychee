<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'Users Management';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'moderation_history_log_system',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Record moderation events made by the system.',
				'details' => 'Holds at upload and NSFW scan results are written to the moderation history.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 12,
				'required_keys' => null,
			],
			[
				'key' => 'moderation_history_log_approvals',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Record approvals in the moderation history.',
				'details' => 'Approvals by an admin or by the system are written to the moderation history.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 13,
				'required_keys' => null,
			],
			[
				'key' => 'moderation_history_log_nsfw_holds',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Record holds caused by NSFW findings.',
				'details' => 'Photos held for review after an NSFW scan are written to the moderation history.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 1,
				'order' => 14,
				'required_keys' => 'ai_vision_enabled,ai_vision_nsfw_enabled,moderation_history_log_system',
			],
		];
	}
};
