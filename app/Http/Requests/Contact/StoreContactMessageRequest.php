<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Contact;

use App\Http\Requests\BaseApiRequest;

class StoreContactMessageRequest extends BaseApiRequest
{
	protected string $name = '';
	protected string $email = '';
	protected string $message_body = '';
	protected string $security_answer = '';
	protected bool $consent_agreed = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'max:255'],
			'message' => ['required', 'string', 'min:10', 'max:5000'],
			'security_answer' => ['sometimes', 'nullable', 'string'],
			'consent_agreed' => ['sometimes', 'boolean'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = $values['name'];
		$this->email = $values['email'];
		$this->message_body = $values['message'];
		$this->security_answer = $values['security_answer'] ?? '';
		$this->consent_agreed = self::toBoolean($values['consent_agreed'] ?? false);
	}

	public function senderName(): string
	{
		return $this->name;
	}

	public function senderEmail(): string
	{
		return $this->email;
	}

	public function senderMessage(): string
	{
		return $this->message_body;
	}

	public function securityAnswer(): string
	{
		return $this->security_answer;
	}

	public function consentAgreed(): bool
	{
		return $this->consent_agreed;
	}
}
