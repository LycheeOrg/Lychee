<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\WebAuthn;

use App\Http\Requests\BaseApiRequest;

/**
 * `POST /WebAuthn::login` (Feature 107, FR-107-05): the browser's signed
 * assertion in its JSON form (FR-107-10). Open to guests.
 */
class LoginCredentialRequest extends BaseApiRequest
{
	/** @var array<string,mixed> */
	private array $public_key_credential;

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
			'id' => 'required|string',
			'rawId' => 'required|string',
			'type' => 'required|string|in:public-key',
			'response' => 'required|array',
			'response.authenticatorData' => 'required|string',
			'response.clientDataJSON' => 'required|string',
			'response.signature' => 'required|string',
			'response.userHandle' => 'sometimes|nullable|string',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->public_key_credential = $values;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function publicKeyCredential(): array
	{
		return $this->public_key_credential;
	}
}
