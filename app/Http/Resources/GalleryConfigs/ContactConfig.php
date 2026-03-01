<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\GalleryConfigs;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ContactConfig extends Data
{
	// Whether the contact form is enabled
	public bool $is_contact_form_enabled;

	// Sample question / answer shown as placeholder
	public string $sample_question;
	public string $sample_answer;

	// Security question (the answer is never exposed)
	public string $security_question;

	// Consent & privacy
	public string $consent_text;
	public string $privacy_policy_url;

	// Submit button label
	public string $submit_button_text;

	public function __construct()
	{
		$this->is_contact_form_enabled = request()->configs()->getValueAsBool('contact_form_enabled');
		$this->sample_question = request()->configs()->getValueAsString('contact_form_sample_question');
		$this->sample_answer = request()->configs()->getValueAsString('contact_form_sample_answer');
		$this->security_question = request()->configs()->getValueAsString('contact_form_security_question');
		$this->consent_text = request()->configs()->getValueAsString('contact_form_custom_consent_text');
		$this->privacy_policy_url = request()->configs()->getValueAsString('contact_form_custom_privacy_url');
		$this->submit_button_text = request()->configs()->getValueAsString('contact_form_custom_submit_button_text');
	}
}
