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
				'key' => 'ai_vision_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable AI Vision features',
				'details' => 'Master toggle for the AI Vision subsystem. When disabled, all AI Vision endpoints and UI elements are inactive.',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 1,
				'order' => 10,
			],
			[
				'key' => 'ai_vision_face_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable facial recognition',
				'details' => 'Enable the facial recognition subsystem. Requires ai_vision_enabled = 1. When disabled, face detection endpoints, People pages, and auto-scan on upload are inactive.',
				'is_expert' => false,
				'is_secret' => false,
				'level' => 1,
				'order' => 11,
			],
			[
				'key' => 'ai_vision_face_permission_mode',
				'value' => 'restricted',
				'cat' => self::CAT,
				'type_range' => 'public|private|privacy-preserving|restricted',
				'description' => 'Permission mode for facial recognition features',
				'details' => 'Controls who can view people, face overlays, and manage faces. Options: public, private, privacy-preserving, restricted.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 12,
			],
			[
				'key' => 'ai_vision_face_selfie_confidence_threshold',
				'value' => '0.8',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Minimum confidence threshold for selfie-based person claim',
				'details' => 'Minimum match confidence score (0.0-1.0) required to automatically link a person via selfie upload.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 13,
			],
			[
				'key' => 'ai_vision_face_person_is_searchable_default',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Default searchability for new persons',
				'details' => 'Default value of the is_searchable flag when a new Person record is created.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 14,
			],
			[
				'key' => 'ai_vision_face_allow_user_claim',
				'value' => '1',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Allow users to claim their person',
				'details' => 'When enabled, regular (non-admin) users may claim a Person record to link it to their account. Admins can always claim/unclaim regardless of this setting.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 15,
			],
			[
				'key' => 'ai_vision_face_scan_batch_size',
				'value' => '200',
				'cat' => self::CAT,
				'type_range' => self::POSITIVE,
				'description' => 'Batch size for bulk face scanning',
				'details' => 'Number of photo IDs dispatched per job chunk when bulk-scanning. Lower values reduce burst queue load.',
				'is_expert' => true,
				'is_secret' => false,
				'level' => 1,
				'order' => 16,
			],
		];
	}
};
