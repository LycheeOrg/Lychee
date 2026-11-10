<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\WebAuthn;

use App\Http\Requests\BaseApiRequest;

/**
 * `POST /WebAuthn::login/options` (Feature 107, FR-107-04): open to guests.
 *
 * `user_id` or `username` (a username, or an email with `email_login_enabled`)
 * narrows the allowed credentials; without either, any discoverable key may answer.
 */
class LoginOptionsRequest extends BaseApiRequest
{
	private ?int $user_id = null;
	private ?string $username = null;

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
			'user_id' => 'sometimes|int',
			'username' => 'sometimes|nullable|string',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->user_id = isset($values['user_id']) ? intval($values['user_id']) : null;
		$this->username = isset($values['username']) && $values['username'] !== '' ? strval($values['username']) : null;
	}

	public function userId(): ?int
	{
		return $this->user_id;
	}

	public function username(): ?string
	{
		return $this->username;
	}
}
