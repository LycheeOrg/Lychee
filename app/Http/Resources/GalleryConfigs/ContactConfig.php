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

	// Security question (the answer is never exposed)
	public string $security_question;

	// Consent & privacy
	public bool $is_consent_required;

	// Pro customization of the submit button text (e.g. for translation)
	public string $header;

	public string $headline;
	public string $consent_text;
	public string $privacy_policy_url;
	public string $submit_button_text;
	public string $contact_method;

	public string $message_label;
	public string $message_answer;

	public string $thank_you_message;

	public function __construct()
	{
		$this->is_contact_form_enabled = request()->configs()->getValueAsBool('contact_form_enabled');
		$this->security_question = request()->configs()->getValueAsString('contact_form_security_question');

		$this->is_consent_required = request()->configs()->getValueAsBool('contact_form_custom_consent_required');

		$this->header = request()->configs()->getValueAsString('contact_form_header');
		$this->headline = request()->configs()->getValueAsString('contact_form_headline');
		$this->consent_text = request()->configs()->getValueAsString('contact_form_custom_consent_text');
		$this->privacy_policy_url = request()->configs()->getValueAsString('contact_form_custom_privacy_url');
		$this->submit_button_text = request()->configs()->getValueAsString('contact_form_custom_submit_button_text');
		$this->contact_method = request()->configs()->getValueAsString('contact_form_contact_method');
		$this->message_label = request()->configs()->getValueAsString('contact_form_message_label');
		$this->message_answer = request()->configs()->getValueAsString('contact_form_message_answer');
		$this->thank_you_message = request()->configs()->getValueAsString('contact_form_thank_you_message');
	}
}
