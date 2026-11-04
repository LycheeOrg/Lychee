<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\WebAuthn;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;

/**
 * `POST /WebAuthn::register` (Feature 107, FR-107-03): the browser's
 * `PublicKeyCredential` for a new key, in its JSON form (FR-107-10).
 */
class RegisterCredentialRequest extends BaseApiRequest
{
	/** @var array<string,mixed> */
	private array $public_key_credential;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Auth::check();
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
			'response.clientDataJSON' => 'required|string',
			'response.attestationObject' => 'required|string',
			'response.transports' => 'sometimes|array',
			'response.transports.*' => 'string',
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
