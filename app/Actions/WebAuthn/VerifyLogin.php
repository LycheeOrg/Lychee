<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\WebAuthn;

use App\Exceptions\WebAuthnVerificationException;
use App\Models\User;
use App\Models\WebAuthnCredential;
use App\Services\WebAuthn\CeremonyStore;
use App\Services\WebAuthn\WebAuthnFactory;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * Verify a login assertion and return the owner of the key (Feature 107, FR-107-05).
 *
 * The key is found by its id, so the user is known before the check. The
 * stored user handle is passed as the expected one: a response without a
 * handle (U2F keys) passes, a response with one must match it. When the
 * options listed credentials, the key must be one of them.
 */
final class VerifyLogin
{
	public function __construct(
		private readonly WebAuthnFactory $factory,
		private readonly CeremonyStore $store,
	) {
	}

	/**
	 * @param array<string,mixed> $payload the browser's `PublicKeyCredential` in JSON form
	 *
	 * @throws WebAuthnVerificationException
	 */
	public function do(array $payload): User
	{
		$options = $this->store->pullRequestOptions();
		$public_key_credential = $this->factory->parseCredential($payload);
		$response = $public_key_credential->response;
		if (!$response instanceof AuthenticatorAssertionResponse) {
			throw new WebAuthnVerificationException('Expected a WebAuthn login response.');
		}

		$stored = $this->findCredential($public_key_credential->rawId);
		$record = $this->validate($stored->toCredentialRecord(), $response, $options);
		$stored->fillFromCredentialRecord($record);
		$stored->save();

		$user = $stored->authenticatable;
		if (!$user instanceof User) {
			throw new WebAuthnVerificationException('Unknown security key.');
		}

		return $user;
	}

	/**
	 * @throws WebAuthnVerificationException
	 */
	private function findCredential(string $raw_id): WebAuthnCredential
	{
		$stored = WebAuthnCredential::query()
			->whereKey(Base64UrlSafe::encodeUnpadded($raw_id))
			->whereNull('disabled_at')
			->first() ?? throw new WebAuthnVerificationException('Unknown security key.');

		if (!$stored->hasReadablePublicKey()) {
			throw new WebAuthnVerificationException('This security key has to be removed and registered again.');
		}

		return $stored;
	}

	/**
	 * @throws WebAuthnVerificationException
	 */
	private function validate(CredentialRecord $record, AuthenticatorAssertionResponse $response, PublicKeyCredentialRequestOptions $options): CredentialRecord
	{
		try {
			return $this->factory->assertionValidator()->check(
				$record,
				$response,
				$options,
				$options->rpId ?? $this->factory->relyingPartyId(),
				$record->userHandle,
			);
		} catch (\Throwable $e) {
			throw new WebAuthnVerificationException('WebAuthn login failed: ' . $e->getMessage(), $e);
		}
	}
}
