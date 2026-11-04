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
 * assertion in its JSON form (FR-107-10). Open to guests, so every field has
 * a length limit far above what an authenticator sends (FR-107-05).
 */
class LoginCredentialRequest extends BaseApiRequest
{
	/** Base64url of the longest credential id WebAuthn allows (1023 bytes). */
	private const MAX_CREDENTIAL_ID = 1364;

	/** Base64url of the longest user handle WebAuthn allows (64 bytes). */
	private const MAX_USER_HANDLE = 86;

	/** Authenticator data, client data and signature: a few hundred characters in practice. */
	private const MAX_RESPONSE_MEMBER = 16384;

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
			'id' => 'required|string|max:' . self::MAX_CREDENTIAL_ID,
			'rawId' => 'required|string|max:' . self::MAX_CREDENTIAL_ID,
			'type' => 'required|string|in:public-key',
			'response' => 'required|array',
			'response.authenticatorData' => 'required|string|max:' . self::MAX_RESPONSE_MEMBER,
			'response.clientDataJSON' => 'required|string|max:' . self::MAX_RESPONSE_MEMBER,
			'response.signature' => 'required|string|max:' . self::MAX_RESPONSE_MEMBER,
			'response.userHandle' => 'sometimes|nullable|string|max:' . self::MAX_USER_HANDLE,
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
