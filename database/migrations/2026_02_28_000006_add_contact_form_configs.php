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
				'key' => 'contact_form_enabled',
				'value' => '0',
				'cat' => self::CAT,
				'type_range' => self::BOOL,
				'description' => 'Enable the contact form on the website',
				'details' => '',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'order' => 1,
			],
			[
				'key' => 'contact_form_sample_question',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Sample question displayed on the contact form',
				'details' => 'Allows you to customize the question shown to the user. Instead of having just "message" and leaving them with a white box, you can ask them to provide specific information.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'order' => 2,
			],
			[
				'key' => 'contact_form_sample_answer',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Sample answer',
				'details' => 'Optional sample answer, shown as placeholder.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'order' => 3,
			],
			[
				'key' => 'contact_form_security_question',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Security question',
				'details' => 'Optional security question, if left empty, no question will be shown and no answer will be expected.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'order' => 4,
			],
			[
				'key' => 'contact_form_security_answer',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Expected answer for the security question',
				'details' => 'The answer to the security question (case-insensitive). Ignored if the security question is empty.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'order' => 5,
			],
			[
				'key' => 'contact_form_custom_consent_text',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Consent text',
				'details' => 'When set, a consent checkbox becomes required. If you need to ask users for consent (e.g. to comply with GDPR), you can set the consent text here. When set, a checkbox will be added to the form and users will have to check it before submitting the form.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'order' => 6,
			],
			[
				'key' => 'contact_form_custom_privacy_url',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'URL to the privacy policy',
				'details' => 'If have a privacy policy URL, you can set it here. It will be displayed as a link alongside the consent text.',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'order' => 7,
			],
			[
				'key' => 'contact_form_custom_submit_button_text',
				'value' => '',
				'cat' => self::CAT,
				'type_range' => self::STRING,
				'description' => 'Custom text for the submit button',
				'details' => 'Default if empty is "Send Message"',
				'is_secret' => false,
				'is_expert' => false,
				'level' => 2,
				'order' => 8,
			],
		];
	}
};
