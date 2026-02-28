<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const CAT = 'contact';

	public function getConfigs(): array
	{
		return [
			[
				'key' => 'contact_form_sample_question',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Sample question displayed to visitors on the contact form.',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 1,
			],
			[
				'key' => 'contact_form_sample_answer',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Sample answer displayed alongside the sample question.',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 2,
			],
			[
				'key' => 'contact_form_security_question',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Security question shown to prevent spam.',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 3,
			],
			[
				'key' => 'contact_form_security_answer',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Expected answer for the security question (case-insensitive).',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 4,
			],
			[
				'key' => 'contact_form_custom_consent_text',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Consent text; when set, a consent checkbox becomes required.',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 5,
			],
			[
				'key' => 'contact_form_custom_privacy_url',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'URL to the privacy policy; displayed as a link with the consent text.',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 6,
			],
			[
				'key' => 'contact_form_custom_submit_button_text',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Custom text for the submit button (default: "Send Message").',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 0,
				'order' => 7,
			],
		];
	}
};
